<?php

namespace App\Services;

use App\Models\AudioSubscription;
use App\Models\BookPart;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AudioSubscriptionService
{
    /**
     * Ouvre l'accès d'écoute à une partie pour la durée achetée, avec le quota d'heures de la formule.
     * Un réabonnement avant échéance prolonge l'accès en cours au lieu de le chevaucher, et ses heures s'ajoutent.
     */
    public function grant(User $user, BookPart $part, int $days, ?Order $order = null): AudioSubscription
    {
        $startsAt = $this->accessEndsAt($user, $part) ?? now();
        $hours = $this->planHours($days);

        return AudioSubscription::create([
            'user_id' => $user->id,
            'book_part_id' => $part->id,
            'order_id' => $order?->id,
            'access_days' => $days,
            'starts_at' => $startsAt,
            'expires_at' => $startsAt->copy()->addDays($days),
            'listening_seconds' => $hours === null ? null : $hours * 3600,
        ]);
    }

    /**
     * Heures d'écoute incluses dans une formule (Paramètres › Contenu › Écoute audio), ou null pour une écoute sans limite.
     */
    public function planHours(int $days): ?int
    {
        $hours = (int) Setting::content('audio_plan_hours_'.$days);

        return $hours > 0 ? $hours : null;
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

    /**
     * Secondes d'écoute restantes sur la partie (abonnements en cours et à venir), ou null si l'écoute est sans limite.
     */
    public function remainingSeconds(User $user, BookPart $part): ?int
    {
        $subscriptions = $this->unexpiredSubscriptions($user, $part);

        if ($subscriptions->contains(fn (AudioSubscription $subscription) => $subscription->listening_seconds === null)) {
            return null;
        }

        return (int) $subscriptions->sum(fn (AudioSubscription $subscription) => $subscription->remainingSeconds());
    }

    /**
     * Écoute permise : un abonnement en cours, et du temps d'écoute restant.
     */
    public function canListen(User $user, BookPart $part): bool
    {
        $subscriptions = $this->unexpiredSubscriptions($user, $part);

        if (! $subscriptions->contains(fn (AudioSubscription $subscription) => $subscription->isActive())) {
            return false;
        }

        $remaining = $this->remainingSeconds($user, $part);

        return $remaining === null || $remaining > 0;
    }

    /**
     * Décompte le temps écouté, en commençant par l'abonnement le plus ancien.
     * Un abonnement en cours sans quota (souscrit avant les quotas) couvre l'écoute : rien n'est décompté.
     */
    public function consume(User $user, BookPart $part, int $seconds): void
    {
        foreach ($this->unexpiredSubscriptions($user, $part) as $subscription) {
            if ($seconds <= 0) {
                return;
            }

            if ($subscription->listening_seconds === null) {
                if ($subscription->isActive()) {
                    return;
                }

                continue;
            }

            $taken = min($seconds, $subscription->remainingSeconds());

            if ($taken > 0) {
                $subscription->increment('listened_seconds', $taken);
                $seconds -= $taken;
            }
        }
    }

    /**
     * @return Collection<int, AudioSubscription>
     */
    private function unexpiredSubscriptions(User $user, BookPart $part): Collection
    {
        return AudioSubscription::where('user_id', $user->id)
            ->where('book_part_id', $part->id)
            ->where('expires_at', '>', now())
            ->orderBy('starts_at')
            ->get();
    }
}
