<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class AuditLog extends Model
{
    use HasFactory, MassPrunable;

    /**
     * Durée de conservation annoncée dans la politique de confidentialité (adresse IP, navigateur).
     */
    public const RETENTION_MONTHS = 12;

    /**
     * Événements qui signalent une activité suspecte, avec leur libellé dans l'administration (Sécurité).
     */
    public const SUSPICIOUS_ACTIONS = [
        'login_failed' => 'Connexion échouée',
        'login_locked' => 'Connexion bloquée (trop d\'essais)',
        'two_factor_failed' => 'Code de double authentification erroné',
        'admin_access_denied' => 'Accès à l\'administration refusé',
        'admin_ip_blocked' => 'Administration ouverte depuis une adresse non autorisée',
        'rate_limited' => 'Trop de requêtes',
        'download_link_invalid' => 'Lien de téléchargement invalide ou expiré',
        'audio_direct_access' => 'Tentative de téléchargement d\'une piste audio',
        'audio_stream_throttled' => 'Lecture audio anormalement rapide',
        'listening_device_replaced' => 'Écoute coupée par un 3e appareil',
        'upload_blocked' => 'Fichier piégé refusé',
        'paystack_webhook_rejected' => 'Notification de paiement non signée',
        'payment_amount_mismatch' => 'Montant payé incohérent',
    ];

    /**
     * Enregistre un événement suspect au plus une fois par fenêtre pour une même clé : un robot ne remplit pas le journal.
     */
    public static function logThrottled(string $key, int $seconds, ?int $userId, string $action, ?array $details = null): void
    {
        // Le journal ne doit jamais empêcher la réponse (refus, limite de débit) de partir, même si la base est indisponible
        try {
            if (Cache::add('audit-throttle:'.$action.':'.$key, true, $seconds)) {
                self::log($userId, $action, $details);
            }
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    public function scopeSuspicious(Builder $query): Builder
    {
        return $query->whereIn('action', array_keys(self::SUSPICIOUS_ACTIONS));
    }

    public function label(): string
    {
        return self::SUSPICIOUS_ACTIONS[$this->action] ?? $this->action;
    }

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'details',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'details' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Entrées supprimées chaque jour par « model:prune » (voir routes/console.php).
     */
    public function prunable(): Builder
    {
        return static::where('created_at', '<', now()->subMonths(self::RETENTION_MONTHS));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function log(?int $userId, string $action, ?array $details = null): self
    {
        return self::create([
            'user_id' => $userId,
            'action' => $action,
            'details' => $details,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }
}
