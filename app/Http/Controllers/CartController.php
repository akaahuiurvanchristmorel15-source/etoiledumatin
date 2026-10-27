<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Services\CartService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(CartService $cartService): View
    {
        $cart = $cartService->getCart();
        $zones = config('shipping.zones');

        return view('pages.cart', compact('cart', 'zones'));
    }

    public function add(Request $request, CartService $cartService): RedirectResponse
    {
        $validated = $request->validate([
            'book_id' => ['required', 'exists:books,id'],
            'format' => ['required', 'in:pdf,physical,audio'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:20'],
            'book_part_id' => ['required_if:format,audio', 'nullable', 'integer'],
            'access_days' => ['required_if:format,audio', 'nullable', 'integer'],
        ]);

        $book = Book::findOrFail($validated['book_id']);
        $format = $validated['format'];
        $quantity = (int) ($validated['quantity'] ?? 1);

        if ($format === 'audio') {
            return $this->addAudioSubscription($book, (int) $validated['book_part_id'], (int) $validated['access_days'], $cartService);
        }

        if ($format === 'pdf' && ! $book->is_pdf_available) {
            return back()->with('error', 'Le format PDF n\'est pas disponible pour ce titre.');
        }

        if ($format === 'physical') {
            if (! $book->is_physical_available || $book->stock_physical < 1) {
                return back()->with('error', 'Le livre physique est actuellement en rupture de stock.');
            }
        }

        $cartService->add($book, $format, $quantity);

        $formatLabel = $format === 'pdf' ? 'e-Book PDF (téléchargement immédiat)' : 'Livre papier broché';

        return redirect()->route('cart.index')->with('success', "« {$book->title} » ({$formatLabel}) a été ajouté à votre panier.");
    }

    /**
     * L'écoute se fait depuis la bibliothèque du lecteur : un compte est indispensable.
     */
    protected function addAudioSubscription(Book $book, int $partId, int $days, CartService $cartService): RedirectResponse
    {
        if (! auth()->check()) {
            session()->put('url.intended', route('catalog.show', $book->slug));

            return redirect()->route('login')->with('info', 'Connectez-vous pour vous abonner à l\'écoute : elle se fait depuis votre bibliothèque.');
        }

        $part = $book->parts()->find($partId);

        if (! $part || ! $book->offersAudioSubscription() || ! array_key_exists($days, $book->audioPlans())) {
            return back()->with('error', 'Cette formule d\'écoute n\'est pas disponible pour ce titre.');
        }

        $cartService->addAudioSubscription($part, $days);

        $planLabel = config('audio.plans.'.$days);

        return redirect()->route('cart.index')->with('success', "Écoute de « {$book->title} » — {$part->label()} ({$planLabel}) ajoutée à votre panier.");
    }

    public function update(Request $request, CartService $cartService): RedirectResponse
    {
        $validated = $request->validate([
            'book_id' => ['required', 'integer'],
            'format' => ['required', 'in:pdf,physical,audio'],
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        $cartService->updateQuantity($validated['book_id'], $validated['format'], $validated['quantity']);

        return back()->with('success', 'Panier mis à jour.');
    }

    public function remove(Request $request, CartService $cartService): RedirectResponse
    {
        $validated = $request->validate([
            'book_id' => ['required', 'integer'],
            'format' => ['required', 'regex:/^(pdf|physical|audio(:\d+)?)$/'],
        ]);

        $cartService->remove($validated['book_id'], $validated['format']);

        return back()->with('success', 'Article retiré du panier.');
    }

    public function setZone(Request $request, CartService $cartService): RedirectResponse
    {
        $validated = $request->validate([
            'shipping_zone' => ['required', 'string', 'in:abidjan,interior,uemoa,international'],
        ]);

        $cartService->setShippingZone($validated['shipping_zone']);

        return back()->with('success', 'Zone de livraison mise à jour.');
    }

    public function clear(CartService $cartService): RedirectResponse
    {
        $cartService->clear();

        return redirect()->route('cart.index')->with('success', 'Votre panier a été vidé.');
    }
}
