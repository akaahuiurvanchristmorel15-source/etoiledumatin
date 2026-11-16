<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\User;
use App\Notifications\SecurityAlert;
use App\Services\SecurityMonitor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendSecurityAlerts extends Command
{
    protected $signature = 'security:alert';

    protected $description = 'Prévient les super administrateurs par e-mail si le journal de sécurité montre une activité anormale.';

    /**
     * Dernier événement du journal déjà analysé : chaque événement n'est signalé qu'une fois.
     */
    private const LAST_EVENT_KEY = 'security-alert:last-event-id';

    /**
     * Fenêtre d'analyse : les événements plus anciens ne déclenchent plus d'alerte.
     */
    private const WINDOW_MINUTES = 60;

    /**
     * Analyse les événements apparus depuis le dernier passage.
     */
    public function handle(SecurityMonitor $monitor): int
    {
        $since = now()->subMinutes(self::WINDOW_MINUTES);
        $now = now();
        $lastEventId = Cache::get(self::LAST_EVENT_KEY);
        $findings = $monitor->findings($since, $lastEventId);

        Cache::forever(self::LAST_EVENT_KEY, (int) AuditLog::max('id'));

        if ($findings === [] || ! config('admin.alerts_enabled')) {
            $this->info('Aucune activité anormale.');

            return self::SUCCESS;
        }

        $recipients = User::where('role', 'super_admin')->get();

        try {
            Notification::send($recipients, new SecurityAlert($findings, $since, $now));
        } catch (\Throwable $exception) {
            Log::error('Alerte de sécurité non envoyée', ['findings' => $findings, 'error' => $exception->getMessage()]);
        }

        $this->warn(count($findings).' constat(s) envoyé(s) à '.$recipients->count().' super administrateur(s).');

        return self::SUCCESS;
    }
}
