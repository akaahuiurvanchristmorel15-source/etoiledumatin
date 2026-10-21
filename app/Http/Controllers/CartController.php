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
        ]);

        $book = Book::findOrFail($validated['book_id']);
        $format = $validated['format'];
        $quantity = (int) ($validated['quantity'] ?? 1);

        if ($format === 'pdf' && ! $book->is_pdf_available) {
            return back()->with('error', 'Le format PDF n\'est pas disponible pour ce titre.');
        }

        if ($format === 'audio' && ! $book->is_audio_available) {
            return back()->with('error', 'Le format Audio n\'est pas disponible pour ce titre.');
        }

        if ($format === 'physical') {
            if (! $book->is_physical_available || $book->stock_physical < 1) {
                return back()->with('error', 'Le livre physique est actuellement en rupture de stock.');
            }
        }

        $cartService->add($book, $format, $quantity);

        $formatLabel = match ($format) {
            'pdf' => 'e-Book PDF (téléchargement immédiat)',
            'audio' => 'Livre Audio MP3 (écoute & téléchargement)',
            default => 'Livre papier broché',
        };

        return redirect()->route('cart.index')->with('success', "« {$book->title} » ({$formatLabel}) a été ajouté à votre panier.");
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
            'format' => ['required', 'in:pdf,physical,audio'],
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
