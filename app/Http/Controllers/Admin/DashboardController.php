<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Book;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Review;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalRevenue = Order::where('payment_status', 'paid')->sum('total_amount');
        $monthRevenue = Order::where('payment_status', 'paid')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('total_amount');

        $paidOrdersCount = Order::where('payment_status', 'paid')->count();
        $pendingLogisticsCount = Order::where('payment_status', 'paid')
            ->whereHas('shippingAddress', fn ($q) => $q->whereIn('status', ['pending', 'processing']))
            ->count();

        // Répartition des ventes
        $pdfSalesCount = OrderItem::where('format', 'pdf')
            ->whereHas('order', fn ($q) => $q->where('payment_status', 'paid'))
            ->sum('quantity');

        $physicalSalesCount = OrderItem::where('format', 'physical')
            ->whereHas('order', fn ($q) => $q->where('payment_status', 'paid'))
            ->sum('quantity');

        // Alertes stocks
        $lowStockBooks = Book::active()
            ->where('is_physical_available', true)
            ->where('stock_physical', '<=', 10)
            ->orderBy('stock_physical', 'asc')
            ->take(5)
            ->get();

        // Dernières commandes
        $recentOrders = Order::with(['shippingAddress', 'items'])
            ->latest()
            ->take(8)
            ->get();

        // Derniers avis à modérer
        $pendingReviewsCount = Review::where('is_approved', false)->count();

        $recentLogs = AuditLog::with('user')
            ->latest()
            ->take(6)
            ->get();

        return view('admin.dashboard', compact(
            'totalRevenue',
            'monthRevenue',
            'paidOrdersCount',
            'pendingLogisticsCount',
            'pdfSalesCount',
            'physicalSalesCount',
            'lowStockBooks',
            'recentOrders',
            'pendingReviewsCount',
            'recentLogs'
        ));
    }
}
