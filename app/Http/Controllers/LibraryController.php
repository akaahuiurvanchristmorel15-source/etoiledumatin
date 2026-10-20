<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PurchasedDownload;
use App\Services\OrderService;
use App\Services\PdfProtectionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class LibraryController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        // Si l'utilisateur est connecté
        $downloads = $user
            ? PurchasedDownload::where('user_id', $user->id)
                ->with(['book.category', 'order'])
                ->latest()
                ->get()
            : collect();

        $orders = $user
            ? Order::where('user_id', $user->id)
                ->with(['items.book', 'shippingAddress'])
                ->latest()
                ->get()
            : collect();

        return view('pages.library', compact('downloads', 'orders'));
    }

    /**
     * Génère un lien de téléchargement temporaire signé à la demande du client
     */
    public function generateDownloadLink(PurchasedDownload $download, PdfProtectionService $pdfService): RedirectResponse
    {
        Gate::authorize('view', $download->order);

        if (! $download->canDownload()) {
            return back()->with('error', 'Vous avez atteint le nombre maximal de téléchargements autorisés pour cet exemplaire ('.$download->max_downloads.'). Veuillez contacter notre service client.');
        }

        $signedUrl = $pdfService->generateSignedDownloadUrl($download, 15);

        return redirect()->away($signedUrl);
    }

    /**
     * Recherche de commande pour les clients invités
     */
    public function guestLookup(Request $request, OrderService $orderService): View
    {
        if (! $request->filled('order_number')) {
            return view('pages.guest-lookup', ['order' => null]);
        }

        $validated = $request->validate([
            'order_number' => ['required', 'string', 'max:50'],
            'contact' => ['required', 'string', 'max:150'],
        ]);

        $contact = trim($validated['contact']);

        $order = Order::where('order_number', trim($validated['order_number']))
            ->where(fn ($query) => $query->where('customer_email', $contact)->orWhere('customer_phone', $contact))
            ->with(['items.book', 'shippingAddress', 'downloads.book'])
            ->first();

        if ($order) {
            $orderService->grantSessionAccess($order);
        }

        return view('pages.guest-lookup', compact('order'));
    }
}
