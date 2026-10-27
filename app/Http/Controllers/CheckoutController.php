<?php

namespace App\Http\Controllers;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    public function index(CartService $cartService): View|RedirectResponse
    {
        $cart = $cartService->getCart();

        if (empty($cart['items'])) {
            return redirect()->route('cart.index')->with('info', 'Votre panier est vide.');
        }

        if ($cart['has_audio'] && ! auth()->check()) {
            session()->put('url.intended', route('checkout.index'));

            return redirect()->route('login')->with('info', 'Connectez-vous pour finaliser : l\'écoute audio se fait depuis votre bibliothèque.');
        }

        $zones = config('shipping.zones');
        $user = auth()->user();

        return view('pages.checkout', compact('cart', 'zones', 'user'));
    }

    public function process(
        Request $request,
        CartService $cartService,
        OrderService $orderService,
        PaymentGatewayInterface $paymentGateway
    ): RedirectResponse {
        $cart = $cartService->getCart();

        if (empty($cart['items'])) {
            return redirect()->route('cart.index')->with('error', 'Votre panier est vide.');
        }

        if ($cart['has_audio'] && ! auth()->check()) {
            session()->put('url.intended', route('checkout.index'));

            return redirect()->route('login')->with('info', 'Connectez-vous pour finaliser : l\'écoute audio se fait depuis votre bibliothèque.');
        }

        // Règles de validation
        $rules = [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['required', 'string', 'max:30'],
            'payment_channel' => ['required', 'in:paystack_momo,paystack_card,paystack'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];

        if ($cart['has_physical']) {
            $rules['delivery_zone'] = ['required', 'string', 'in:abidjan,interior,uemoa,international'];
            $rules['address_line'] = ['required', 'string', 'max:255'];
            $rules['city'] = ['required', 'string', 'max:100'];
            $rules['country'] = ['required', 'string', 'max:100'];
            $rules['delivery_instructions'] = ['nullable', 'string', 'max:500'];
        }

        $validated = $request->validate($rules);

        // Le stock a pu baisser depuis l'ajout au panier : on refuse plutôt que de vendre des livres absents
        if ($unavailable = $cartService->unavailableItems()) {
            return redirect()->route('cart.index')->with('error', 'Stock insuffisant pour : '.implode(', ', $unavailable).'. Ajustez les quantités avant de payer.');
        }

        // Mettre à jour la zone de livraison si spécifiée
        if ($cart['has_physical'] && isset($validated['delivery_zone'])) {
            $cartService->setShippingZone($validated['delivery_zone']);
        }

        // Créer la commande
        $order = $orderService->createOrderFromCart($cartService, $validated, auth()->user());
        $orderService->grantSessionAccess($order);

        // Initialiser le paiement avec Paystack
        try {
            $paymentData = $paymentGateway->initializePayment($order, route('checkout.callback'));
        } catch (\Throwable $e) {
            Log::error('Initialisation du paiement impossible', ['order_id' => $order->id, 'message' => $e->getMessage()]);

            return redirect()->route('cart.index')->with('error', 'Le service de paiement est momentanément indisponible. Veuillez réessayer dans quelques instants.');
        }

        // Sauvegarder la référence de paiement
        $order->update([
            'payment_reference' => $paymentData['reference'],
        ]);

        return redirect()->away($paymentData['authorization_url']);
    }

    public function callback(
        Request $request,
        PaymentGatewayInterface $paymentGateway,
        OrderService $orderService
    ): RedirectResponse {
        $reference = $request->query('reference') ?? $request->query('trxref');

        if (! $reference) {
            return redirect()->route('cart.index')->with('error', 'Référence de transaction introuvable.');
        }

        // La commande est retrouvée uniquement via la référence générée lors de l'initialisation
        $order = Order::where('payment_reference', $reference)->first();

        if (! $order) {
            return redirect()->route('home')->with('error', 'Commande introuvable.');
        }

        if ($order->isPaid()) {
            return redirect()->route('checkout.confirmation', $order);
        }

        $verification = $paymentGateway->verifyTransaction($reference);

        if ($verification['success'] && ! $this->coversOrderTotal($order, $verification['amount'])) {
            Log::warning('Montant Paystack inférieur au total de la commande', [
                'order_id' => $order->id,
                'expected' => $order->total_amount,
                'received' => $verification['amount'],
            ]);

            return redirect()->route('cart.index')->with('error', 'Le montant payé ne correspond pas au total de la commande. Veuillez contacter notre service client.');
        }

        if ($verification['success']) {
            $orderService->completeOrderPayment(
                $order,
                $reference,
                $verification['raw_data']['channel'] ?? 'paystack'
            );

            return redirect()->route('checkout.confirmation', $order)
                ->with('success', 'Félicitations ! Votre paiement a été validé avec succès.');
        }

        return redirect()->route('cart.index')->with('error', 'Le paiement n\'a pas pu être validé : '.$verification['message']);
    }

    public function confirmation(Order $order): View
    {
        Gate::authorize('view', $order);

        $order->load(['items.book', 'shippingAddress', 'downloads.book']);

        return view('pages.confirmation', compact('order'));
    }

    /**
     * Interface de simulation de paiement Paystack en mode sandbox/test
     */
    public function sandboxPayment(Order $order, PaymentGatewayInterface $paymentGateway): View
    {
        $this->ensureSandboxIsUsable($order, $paymentGateway);

        $ref = $order->payment_reference;

        return view('pages.sandbox-checkout', compact('order', 'ref'));
    }

    public function sandboxConfirm(
        Order $order,
        Request $request,
        OrderService $orderService,
        PaymentGatewayInterface $paymentGateway
    ): RedirectResponse {
        $this->ensureSandboxIsUsable($order, $paymentGateway);

        $validated = $request->validate([
            'payment_channel' => ['required', 'string', 'in:Orange Money,MTN Mobile Money,Moov Money,Wave,Carte Bancaire Visa/Mastercard'],
            'reference' => ['required', 'string', 'in:'.$order->payment_reference],
        ]);

        $orderService->completeOrderPayment(
            $order,
            $validated['reference'],
            'paystack_sandbox ('.$validated['payment_channel'].')'
        );

        return redirect()->route('checkout.confirmation', $order)
            ->with('success', 'Paiement Paystack simulé avec succès via '.$validated['payment_channel'].' !');
    }

    /**
     * Le paiement simulé n'existe qu'en développement, pour une commande en attente appartenant au visiteur.
     */
    private function ensureSandboxIsUsable(Order $order, PaymentGatewayInterface $paymentGateway): void
    {
        abort_unless($paymentGateway->isSimulationEnabled(), 404);
        abort_if($order->isPaid() || empty($order->payment_reference), 404);

        Gate::authorize('view', $order);
    }

    /**
     * Vérifie que le montant encaissé (en FCFA) couvre le total de la commande.
     */
    private function coversOrderTotal(Order $order, int $amountPaid): bool
    {
        return $amountPaid >= (int) $order->total_amount;
    }

    /**
     * Webhook Paystack officiel avec vérification HMAC SHA-512
     */
    public function webhook(
        Request $request,
        PaymentGatewayInterface $paymentGateway,
        OrderService $orderService
    ): JsonResponse {
        $result = $paymentGateway->handleWebhook($request);

        if (! $result['verified']) {
            return response()->json(['message' => 'Signature invalide'], 400);
        }

        if ($result['event'] === 'charge.success' && ! empty($result['reference'])) {
            $order = Order::where('payment_reference', $result['reference'])->first();

            $amountPaid = (int) (($result['data']['amount'] ?? 0) / 100);

            $currency = $result['data']['currency'] ?? null;

            if ($order && ! $order->isPaid() && $this->coversOrderTotal($order, $amountPaid) && strtoupper((string) $currency) === strtoupper((string) config('paystack.currency', 'XOF'))) {
                $orderService->completeOrderPayment(
                    $order,
                    $result['reference'],
                    $result['data']['channel'] ?? 'paystack'
                );
            }
        }

        return response()->json(['status' => 'success'], 200);
    }
}
