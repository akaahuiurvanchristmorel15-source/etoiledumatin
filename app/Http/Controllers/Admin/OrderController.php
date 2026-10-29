<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $query = Order::with(['items.book', 'shippingAddress', 'user']);

        if ($paymentStatus = $this->queryText($request, 'status', 20)) {
            $query->where('payment_status', $paymentStatus);
        }

        if ($shippingStatus = $this->queryText($request, 'shipping_status', 20)) {
            $query->whereHas('shippingAddress', function ($q) use ($shippingStatus) {
                $q->where('status', $shippingStatus);
            });
        }

        if ($search = $this->queryText($request, 'search')) {
            $s = '%'.$search.'%';
            $query->where(function ($q) use ($s) {
                $q->where('order_number', 'like', $s)
                    ->orWhere('customer_name', 'like', $s)
                    ->orWhere('customer_email', 'like', $s)
                    ->orWhere('customer_phone', 'like', $s);
            });
        }

        $orders = $query->latest()->paginate(15)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $order->load(['items.book.category', 'shippingAddress', 'downloads.book', 'user']);

        return view('admin.orders.show', compact('order'));
    }

    public function updateShipping(Request $request, Order $order): RedirectResponse
    {
        $shipping = $order->shippingAddress;

        if (! $shipping) {
            return back()->with('error', 'Cette commande ne contient aucun article physique à expédier.');
        }

        $validated = $request->validate([
            'status' => ['required', 'in:pending,processing,shipped,delivered,cancelled'],
            'carrier_name' => ['nullable', 'string', 'max:100'],
            'tracking_number' => ['nullable', 'string', 'max:100'],
        ]);

        if ($validated['status'] === 'shipped' && empty($shipping->shipped_at)) {
            $shipping->shipped_at = now();
        }

        if ($validated['status'] === 'delivered' && empty($shipping->delivered_at)) {
            $shipping->delivered_at = now();
        }

        $shipping->update($validated);

        AuditLog::log(auth()->id(), 'shipping_updated', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $validated['status'],
            'tracking' => $validated['tracking_number'] ?? null,
        ]);

        return back()->with('success', "Le statut d'expédition de la commande {$order->order_number} a été mis à jour.");
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $orders = Order::where('payment_status', 'paid')
            ->with(['items', 'shippingAddress'])
            ->latest()
            ->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="journal-ventes-'.date('Y-m-d').'.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($orders) {
            $handle = fopen('php://output', 'w');
            // BOM UTF-8 pour Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'N° Commande',
                'Date',
                'Client',
                'Email',
                'Téléphone',
                'Total PDF (FCFA)',
                'Total Physique (FCFA)',
                'Frais de Port (FCFA)',
                'Total Général (FCFA)',
                'Moyen de Paiement',
                'Référence Paystack',
                'Statut Expédition',
            ], ';');

            foreach ($orders as $order) {
                fputcsv($handle, [
                    $order->order_number,
                    $order->paid_at?->format('d/m/Y H:i') ?? $order->created_at->format('d/m/Y H:i'),
                    $order->customer_name,
                    $order->customer_email,
                    $order->customer_phone,
                    $order->subtotal_pdf,
                    $order->subtotal_physical,
                    $order->shipping_cost,
                    $order->total_amount,
                    $order->payment_method,
                    $order->payment_reference,
                    $order->shippingAddress?->status_label ?? 'N/A (100% numérique)',
                ], ';');
            }

            fclose($handle);
        }, 200, $headers);
    }
}
