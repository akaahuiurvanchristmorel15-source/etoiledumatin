<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class NotificationController extends Controller
{
    /**
     * Commandes payées depuis la dernière vérification, interrogé toutes les 30 secondes par l'administration.
     */
    public function orders(Request $request): JsonResponse
    {
        $validated = $request->validate(['since' => ['required', 'date']]);
        $checkedAt = now();

        $orders = Order::where('payment_status', 'paid')
            ->where('paid_at', '>', Carbon::parse($validated['since']))
            ->where('paid_at', '<=', $checkedAt)
            ->latest('paid_at')
            ->take(10)
            ->get();

        return response()->json([
            'checked_at' => $checkedAt->toIso8601String(),
            'orders' => $orders->map(fn (Order $order) => [
                'number' => $order->order_number,
                'customer' => $order->customer_name,
                'total' => $order->formatted_total,
                'url' => route('admin.orders.show', $order),
            ]),
        ]);
    }
}
