<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BookPart;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class CartService
{
    protected const SESSION_KEY = 'letoile_cart';

    protected const SHIPPING_ZONE_KEY = 'letoile_shipping_zone';

    /**
     * Préfixe des lignes d'abonnement audio dans le panier : « audio:{id de la partie} ».
     */
    public const AUDIO_KEY_PREFIX = 'audio:';

    /**
     * Récupère le contenu actuel du panier avec les modèles de livres associés.
     */
    public function getCart(): array
    {
        $rawCart = Session::get(self::SESSION_KEY, []);
        $items = [];
        $subtotalPdf = 0;
        $subtotalPhysical = 0;
        $subtotalAudio = 0;
        $hasPhysical = false;
        $hasPdf = false;
        $hasAudio = false;

        $bookIds = array_keys($rawCart);
        $books = Book::whereIn('id', $bookIds)->get()->keyBy('id');
        $partIds = collect($rawCart)->flatMap(fn (array $formats) => array_keys($formats))
            ->filter(fn (string $key) => str_starts_with($key, self::AUDIO_KEY_PREFIX))
            ->map(fn (string $key) => (int) Str::after($key, self::AUDIO_KEY_PREFIX));
        $parts = BookPart::whereIn('id', $partIds)->get()->keyBy('id');

        foreach ($rawCart as $bookId => $formats) {
            if (! $books->has($bookId)) {
                continue;
            }
            $book = $books->get($bookId);

            foreach ($formats as $cartKey => $itemData) {
                $format = $cartKey;
                $quantity = (int) ($itemData['quantity'] ?? 1);
                $part = null;
                $accessDays = null;

                if (str_starts_with($cartKey, self::AUDIO_KEY_PREFIX)) {
                    $part = $parts->get((int) Str::after($cartKey, self::AUDIO_KEY_PREFIX));
                    $accessDays = (int) ($itemData['days'] ?? 0);
                    $unitPrice = $book->audioPlans()[$accessDays] ?? 0;

                    // Partie supprimée ou formule retirée de la vente depuis l'ajout au panier
                    if (! $part || (int) $part->book_id !== $book->id || $unitPrice === 0) {
                        continue;
                    }

                    $format = 'audio';
                    $hasAudio = true;
                    $quantity = 1;
                    $lineTotal = $unitPrice;
                    $subtotalAudio += $lineTotal;
                } elseif ($format === 'audio') {
                    // Ancien achat MP3 définitif, remplacé par l'abonnement d'écoute
                    continue;
                } elseif ($format === 'pdf') {
                    $hasPdf = true;
                    $unitPrice = $book->price_pdf;
                    $lineTotal = $unitPrice; // Quantité toujours 1 pour un e-book
                    $subtotalPdf += $lineTotal;
                } else {
                    $hasPhysical = true;
                    $unitPrice = $book->price_physical;
                    $lineTotal = $unitPrice * $quantity;
                    $subtotalPhysical += $lineTotal;
                }

                $items[] = [
                    'book_id' => $book->id,
                    'book' => $book,
                    'cart_key' => $cartKey,
                    'format' => $format,
                    'book_part' => $part,
                    'book_part_id' => $part?->id,
                    'access_days' => $accessDays,
                    'format_label' => $part
                        ? 'Écoute audio · Partie '.$part->position.' · '.config('audio.plans.'.$accessDays)
                        : ($format === 'pdf' ? 'e-Book PDF' : 'Livre papier'),
                    'quantity' => $quantity,
                    'price' => $unitPrice,
                    'unit_price' => $unitPrice,
                    'total_price' => $lineTotal,
                ];
            }
        }

        $shippingZone = $this->getShippingZone();
        $shippingCost = $hasPhysical ? $this->calculateShippingCost($shippingZone) : 0;
        $total = $subtotalPdf + $subtotalPhysical + $subtotalAudio + $shippingCost;

        return [
            'items' => $items,
            'subtotal_pdf' => $subtotalPdf,
            'subtotal_physical' => $subtotalPhysical,
            'subtotal_audio' => $subtotalAudio,
            'shipping_zone' => $shippingZone,
            'shipping_cost' => $shippingCost,
            'total' => $total,
            'has_pdf' => $hasPdf,
            'has_physical' => $hasPhysical,
            'has_audio' => $hasAudio,
            'count' => count($items),
        ];
    }

    /**
     * Ajoute un livre au panier selon le format souhaité.
     */
    public function add(Book $book, string $format = 'pdf', int $quantity = 1): void
    {
        $cart = Session::get(self::SESSION_KEY, []);

        if (! isset($cart[$book->id])) {
            $cart[$book->id] = [];
        }

        if ($format === 'pdf') {
            $cart[$book->id]['pdf'] = ['quantity' => 1];
        } else {
            $currentQty = $cart[$book->id]['physical']['quantity'] ?? 0;
            $newQty = $currentQty + $quantity;
            // Limiter par le stock disponible
            if ($book->stock_physical > 0 && $newQty > $book->stock_physical) {
                $newQty = $book->stock_physical;
            }
            $cart[$book->id]['physical'] = ['quantity' => max(1, $newQty)];
        }

        Session::put(self::SESSION_KEY, $cart);
    }

    /**
     * Ajoute un abonnement d'écoute à une partie du livre ; choisir une autre durée remplace la précédente.
     */
    public function addAudioSubscription(BookPart $part, int $days): void
    {
        $cart = Session::get(self::SESSION_KEY, []);

        $cart[$part->book_id][self::AUDIO_KEY_PREFIX.$part->id] = ['quantity' => 1, 'days' => $days];

        Session::put(self::SESSION_KEY, $cart);
    }

    /**
     * Met à jour la quantité d'un article physique.
     */
    public function updateQuantity(int $bookId, string $format, int $quantity): void
    {
        $cart = Session::get(self::SESSION_KEY, []);

        if (isset($cart[$bookId][$format])) {
            if ($format !== 'physical') {
                $cart[$bookId][$format]['quantity'] = 1;
            } else {
                $book = Book::find($bookId);
                $max = $book ? $book->stock_physical : 99;
                $quantity = min(max(1, $quantity), $max);
                $cart[$bookId][$format]['quantity'] = $quantity;
            }
            Session::put(self::SESSION_KEY, $cart);
        }
    }

    /**
     * Retire un élément du panier.
     */
    public function remove(int $bookId, string $format): void
    {
        $cart = Session::get(self::SESSION_KEY, []);

        if (isset($cart[$bookId][$format])) {
            unset($cart[$bookId][$format]);
            if (empty($cart[$bookId])) {
                unset($cart[$bookId]);
            }
            Session::put(self::SESSION_KEY, $cart);
        }
    }

    /**
     * Titres des livres papier du panier dont la quantité demandée dépasse le stock disponible.
     *
     * @return array<int, string>
     */
    public function unavailableItems(): array
    {
        return collect($this->getCart()['items'])
            ->filter(fn (array $item) => $item['format'] === 'physical'
                && (! $item['book']->is_physical_available || $item['quantity'] > $item['book']->stock_physical))
            ->map(fn (array $item) => $item['book']->title)
            ->values()
            ->all();
    }

    /**
     * Vide complètement le panier.
     */
    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    /**
     * Définit la zone de livraison sélectionnée.
     */
    public function setShippingZone(string $zone): void
    {
        $zones = config('shipping.zones');
        if (array_key_exists($zone, $zones)) {
            Session::put(self::SHIPPING_ZONE_KEY, $zone);
        }
    }

    public function getShippingZone(): string
    {
        return Session::get(self::SHIPPING_ZONE_KEY, 'abidjan');
    }

    public function calculateShippingCost(string $zone): int
    {
        $zones = config('shipping.zones');

        return $zones[$zone]['price'] ?? 1500;
    }
}
