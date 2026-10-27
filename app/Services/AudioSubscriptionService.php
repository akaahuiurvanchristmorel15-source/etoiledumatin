<?php

namespace App\Services;

use App\Models\AudioSubscription;
use App\Models\BookPart;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Carbon;

class AudioSubscriptionService
{
    /**
     * Ouvre l'accès d'écoute à une partie pour la durée achetée.
     * Un réabonnement avant échéance prolonge l'accès en cours au lieu de le chevaucher.
     */
    public function grant(User $user, BookPart $part, int $days, ?Order $order = null): AudioSubscription
    {
        $startsAt = $this->accessEndsAt($user, $part) ?? now();

        return AudioSubscription::create([
            'user_id' => $user->id,
            'book_part_id' => $part->id,
            'order_id' => $order?->id,
            'access_days' => $days,
            'starts_at' => $startsAt,
            'expires_at' => $startsAt->copy()->addDays($days),
        ]);
    }

    /**
     * Date de fin de l'accès cumulé (abonnements en cours et à venir), ou null s'il est échu.
     */
    public function accessEndsAt(User $user, BookPart $part): ?Carbon
    {
        $expiry = AudioSubscription::where('user_id', $user->id)
            ->where('book_part_id', $part->id)
            ->where('expires_at', '>', now())
            ->max('expires_at');

        return $expiry ? Carbon::parse($expiry) : null;
    }
}
