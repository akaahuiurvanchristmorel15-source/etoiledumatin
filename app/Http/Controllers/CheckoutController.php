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

class CheckoutController extends Controller
{
    public function index(CartService $cartService): View|RedirectResponse
    {
        $cart = $cartService->getCart();

        if (empty($cart['items'])) {
            return redirect()->route('cart.index')->with('info', 'Votre panier est vide.');
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

        // Mettre à jour la zone de livraison si spécifiée
        if ($cart['has_physical'] && isset($validated['delivery_zone'])) {
            $cartService->setShippingZone($validated['delivery_zone']);
        }

        // Créer la commande
        $order = $orderService->createOrderFromCart($cartService, $validated, auth()->user());

        // Initialiser le paiement avec Paystack
        $callbackUrl = route('checkout.callback', ['order_id' => $order->id]);
        $paymentData = $paymentGateway->initializePayment($order, $callbackUrl);

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
        $orderId = $request->query('order_id');

        if (! $reference) {
            return redirect()->route('cart.index')->with('error', 'Référence de transaction introuvable.');
        }

        $order = $orderId ? Order::find($orderId) : Order::where('payment_reference', $reference)->first();

        if (! $order) {
            return redirect()->route('home')->with('error', 'Commande introuvable.');
        }

        $verification = $paymentGateway->verifyTransaction($reference);

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
        // Autoriser si l'utilisateur est le propriétaire ou pour les commandes d'invités
        $order->load(['items.book', 'shippingAddress', 'downloads.book']);

        return view('pages.confirmation', compact('order'));
    }

    /**
     * Interface de simulation de paiement Paystack en mode sandbox/test
     */
    public function sandboxPayment(Order $order, Request $request): View
    {
        $ref = $request->query('ref', 'SANDBOX_'.uniqid());

        return view('pages.sandbox-checkout', compact('order', 'ref'));
    }

    public function sandboxConfirm(
        Order $order,
        Request $request,
        OrderService $orderService
    ): RedirectResponse {
        $validated = $request->validate([
            'channel' => ['required', 'string', 'in:Orange Money,MTN Mobile Money,Moov Money,Wave,Carte Bancaire Visa/Mastercard'],
            'reference' => ['required', 'string'],
        ]);

        $orderService->completeOrderPayment(
            $order,
            $validated['reference'],
            'paystack_sandbox ('.$validated['channel'].')'
        );

        return redirect()->route('checkout.confirmation', $order)
            ->with('success', 'Paiement Paystack simulé avec succès via '.$validated['channel'].' !');
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

            if ($order && ! $order->isPaid()) {
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
