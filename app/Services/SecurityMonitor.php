<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Lecture du journal de sécurité : repère les comportements qui méritent l'attention de l'équipe.
 */
class SecurityMonitor
{
    /**
     * Connexions échouées depuis une même adresse au-delà desquelles on alerte.
     */
    public const FAILED_LOGINS_PER_IP = 10;

    /**
     * Tentatives de copie d'audio (accès direct, lecture trop rapide) par un même compte.
     */
    public const AUDIO_ABUSE_PER_USER = 3;

    /**
     * Coupures « 3e appareil » par un même compte : au-delà, le compte est probablement partagé.
     */
    public const DEVICE_REPLACEMENTS_PER_USER = 5;

    /**
     * Constats de la période, du plus grave au plus courant ; vide si rien d'anormal.
     *
     * @return list<string>
     */
    public function findings(Carbon $since, ?int $afterId = null): array
    {
        $events = AuditLog::suspicious()
            ->where('created_at', '>=', $since)
            ->when($afterId, fn ($query, int $id) => $query->where('id', '>', $id))
            ->with('user:id,email,role')
            ->get();

        return array_values(array_filter([
            ...$this->lockedStaffAccounts($events),
            ...$this->count($events, 'upload_blocked', fn (int $count) => "{$count} fichier(s) piégé(s) refusé(s) à l'envoi dans l'administration."),
            ...$this->count($events, 'admin_ip_blocked', fn (int $count) => "{$count} accès à l'administration depuis une adresse non autorisée."),
            ...$this->count($events, 'admin_access_denied', fn (int $count) => "{$count} tentative(s) d'ouvrir l'administration avec un compte client."),
            ...$this->count($events, 'payment_amount_mismatch', fn (int $count) => "{$count} paiement(s) dont le montant ne correspond pas à la commande."),
            ...$this->count($events, 'paystack_webhook_rejected', fn (int $count) => "{$count} notification(s) de paiement sans signature valide (tentative de fausse confirmation ?)."),
            ...$this->failedLoginsByIp($events),
            ...$this->byUser($events, ['audio_direct_access', 'audio_stream_throttled'], self::AUDIO_ABUSE_PER_USER, fn (string $email, int $count) => "Le compte {$email} a tenté {$count} fois de copier une piste audio."),
            ...$this->byUser($events, ['listening_device_replaced'], self::DEVICE_REPLACEMENTS_PER_USER, fn (string $email, int $count) => "Le compte {$email} a dépassé {$count} fois la limite de deux appareils : il est peut-être partagé."),
        ]));
    }

    /**
     * @param  Collection<int, AuditLog>  $events
     * @return list<string>
     */
    private function lockedStaffAccounts(Collection $events): array
    {
        $emails = $events->where('action', 'login_locked')->pluck('details.email')->filter()->unique();
        $staffEmails = User::whereIn('email', $emails)->get()->filter->isStaff()->pluck('email');

        return $staffEmails->map(fn (string $email) => "Le compte de l'équipe {$email} a été bloqué après trop de mots de passe erronés.")->values()->all();
    }

    /**
     * @param  Collection<int, AuditLog>  $events
     * @return list<string>
     */
    private function failedLoginsByIp(Collection $events): array
    {
        return $events->whereIn('action', ['login_failed', 'two_factor_failed'])
            ->groupBy('ip_address')
            ->filter(fn (Collection $group) => $group->count() >= self::FAILED_LOGINS_PER_IP)
            ->map(fn (Collection $group, string $ip) => "{$group->count()} connexions échouées depuis l'adresse {$ip}.")
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, AuditLog>  $events
     * @param  callable(int): string  $message
     * @return list<string>
     */
    private function count(Collection $events, string $action, callable $message): array
    {
        $count = $events->where('action', $action)->count();

        return $count > 0 ? [$message($count)] : [];
    }

    /**
     * @param  Collection<int, AuditLog>  $events
     * @param  list<string>  $actions
     * @param  callable(string, int): string  $message
     * @return list<string>
     */
    private function byUser(Collection $events, array $actions, int $threshold, callable $message): array
    {
        return $events->whereIn('action', $actions)
            ->filter(fn (AuditLog $event) => $event->user)
            ->groupBy('user_id')
            ->filter(fn (Collection $group) => $group->count() >= $threshold)
            ->map(fn (Collection $group) => $message($group->first()->user->email, $group->count()))
            ->values()
            ->all();
    }
}
