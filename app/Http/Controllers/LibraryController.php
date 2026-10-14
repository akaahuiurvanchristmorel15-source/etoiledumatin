<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PurchasedDownload;
use App\Services\PdfProtectionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
        // Vérification de sécurité
        if (auth()->check() && $download->user_id && $download->user_id !== auth()->id() && ! auth()->user()->isAdmin()) {
            abort(403, 'Accès non autorisé à ce titre.');
        }

        if (! $download->canDownload()) {
            return back()->with('error', 'Vous avez atteint le nombre maximal de téléchargements autorisés pour cet exemplaire ('.$download->max_downloads.'). Veuillez contacter notre service client.');
        }

        $signedUrl = $pdfService->generateSignedDownloadUrl($download, 15);

        return redirect()->away($signedUrl);
    }

    /**
     * Recherche de commande pour les clients invités
     */
    public function guestLookup(Request $request): View|RedirectResponse
    {
        $validated = $request->validate([
            'order_number' => ['required', 'string'],
            'email' => ['required', 'email'],
        ]);

        $order = Order::where('order_number', trim($validated['order_number']))
            ->where('customer_email', trim($validated['email']))
            ->with(['items.book', 'shippingAddress', 'downloads.book'])
            ->first();

        if (! $order) {
            return back()->with('error', 'Aucune commande ne correspond à ce numéro et cette adresse email.');
        }

        return view('pages.order-status', compact('order'));
    }
}
