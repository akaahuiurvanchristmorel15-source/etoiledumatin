<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PurchasedDownload;
use App\Models\ShippingAddress;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    /**
     * Clé de session listant les commandes que le visiteur courant a passées ou retrouvées.
     */
    public const SESSION_ACCESS_KEY = 'letoile_accessible_orders';

    public function __construct(protected AudioSubscriptionService $audioSubscriptions) {}

    /**
     * Autorise la session courante à consulter la commande (utile pour les clients invités).
     */
    public function grantSessionAccess(Order $order): void
    {
        $orderIds = session()->get(self::SESSION_ACCESS_KEY, []);

        if (! in_array($order->id, $orderIds, true)) {
            session()->put(self::SESSION_ACCESS_KEY, [...$orderIds, $order->id]);
        }
    }

    /**
     * Crée une commande en base de données à partir du panier courant.
     */
    public function createOrderFromCart(CartService $cartService, array $customerData, ?User $user = null): Order
    {
        $cart = $cartService->getCart();

        if (empty($cart['items'])) {
            throw new \InvalidArgumentException('Le panier est vide.');
        }

        return DB::transaction(function () use ($cart, $customerData, $user, $cartService) {
            $order = Order::create([
                'user_id' => $user?->id,
                'customer_name' => $customerData['name'],
                'customer_email' => $customerData['email'],
                'customer_phone' => $customerData['phone'],
                'subtotal_pdf' => $cart['subtotal_pdf'],
                'subtotal_physical' => $cart['subtotal_physical'],
                'subtotal_audio' => $cart['subtotal_audio'] ?? 0,
                'shipping_cost' => $cart['shipping_cost'],
                'total_amount' => $cart['total'],
                'currency' => 'XOF',
                'payment_status' => 'pending',
                'payment_method' => $customerData['payment_channel'] ?? 'paystack',
                'notes' => $customerData['notes'] ?? null,
            ]);

            foreach ($cart['items'] as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'book_id' => $item['book_id'],
                    'book_part_id' => $item['book_part_id'] ?? null,
                    'format' => $item['format'],
                    'access_days' => $item['access_days'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['total_price'],
                ]);
            }

            // Si la commande contient des livres physiques, on enregistre l'adresse de livraison
            if ($cart['has_physical']) {
                ShippingAddress::create([
                    'order_id' => $order->id,
                    'recipient_name' => $customerData['recipient_name'] ?? $customerData['name'],
                    'phone' => $customerData['recipient_phone'] ?? $customerData['phone'],
                    'delivery_zone' => $customerData['delivery_zone'] ?? $cart['shipping_zone'],
                    'address_line' => $customerData['address_line'] ?? 'Adresse non précisée',
                    'city' => $customerData['city'] ?? 'Abidjan',
                    'country' => $customerData['country'] ?? 'Côte d\'Ivoire',
                    'instructions' => $customerData['delivery_instructions'] ?? null,
                    'status' => 'pending',
                ]);
            }

            // Vidage du panier
            $cartService->clear();

            AuditLog::log($user?->id, 'order_created', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'total' => $order->total_amount,
            ]);

            return $order;
        });
    }

    /**
     * Valide le paiement de la commande et déclenche les actions post-achat :
     * 1. Décrémentation des stocks physiques.
     * 2. Génération des accès aux livres PDF et des abonnements d'écoute dans « Ma Bibliothèque ».
     * 3. Traces d'audit.
     */
    public function completeOrderPayment(Order $order, string $paymentReference, string $paymentMethod = 'paystack'): void
    {
        if ($order->isPaid()) {
            return;
        }

        DB::transaction(function () use ($order, $paymentReference, $paymentMethod) {
            // Verrou pessimiste : le retour client et le webhook peuvent arriver simultanément
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($order->isPaid()) {
                return;
            }

            $order->update([
                'payment_status' => 'paid',
                'payment_reference' => $paymentReference,
                'payment_method' => $paymentMethod,
                'paid_at' => now(),
            ]);

            // Traitement de chaque article
            foreach ($order->items as $item) {
                if ($item->isPhysical()) {
                    // Décrémenter le stock disponible
                    $item->book()->decrement('stock_physical', min($item->book->stock_physical, $item->quantity));
                } elseif ($item->isAudioSubscription()) {
                    // Ouvrir l'écoute de la partie dans « Ma Bibliothèque » pour la durée achetée
                    if ($order->user && $item->part) {
                        $this->audioSubscriptions->grant($order->user, $item->part, $item->access_days, $order);
                    }
                } elseif ($item->isPdf() || $item->isAudio()) {
                    // Créer l'accès de téléchargement ou écoute sécurisé
                    PurchasedDownload::firstOrCreate(
                        [
                            'order_id' => $order->id,
                            'book_id' => $item->book_id,
                            'format' => $item->format,
                        ],
                        [
                            'user_id' => $order->user_id,
                            'customer_name' => $order->customer_name,
                            'customer_email' => $order->customer_email,
                            'download_token' => Str::random(40),
                            'download_count' => 0,
                            'max_downloads' => 15,
                        ]
                    );
                }
            }

            // Si la commande a des articles physiques, passer le statut d'expédition à "À préparer"
            if ($order->shippingAddress) {
                $order->shippingAddress->update([
                    'status' => 'pending',
                ]);
            }

            AuditLog::log($order->user_id, 'order_paid', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'reference' => $paymentReference,
                'method' => $paymentMethod,
                'amount' => $order->total_amount,
            ]);
        });

        $order->refresh();
    }
}
