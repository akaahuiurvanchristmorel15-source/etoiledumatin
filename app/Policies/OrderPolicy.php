<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use App\Services\OrderService;

class OrderPolicy
{
    /**
     * Autorise la consultation d'une commande (confirmation, téléchargements) :
     * personnel d'administration, client propriétaire, ou session ayant passé / retrouvé la commande.
     */
    public function view(?User $user, Order $order): bool
    {
        if ($user?->isStaff()) {
            return true;
        }

        if ($user && $order->user_id === $user->id) {
            return true;
        }

        return in_array($order->id, session()->get(OrderService::SESSION_ACCESS_KEY, []), true);
    }
}
