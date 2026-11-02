<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\ProformaInvoicePdf;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class OrderInvoiceController extends Controller
{
    /**
     * Télécharge la facture proforma : mêmes droits que la page de la commande.
     */
    public function __invoke(Order $order): Response
    {
        Gate::authorize('view', $order);

        $order->load(['items.book', 'items.part', 'shippingAddress']);

        return response((new ProformaInvoicePdf($order))->render(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="facture-proforma-'.$order->order_number.'.pdf"',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
