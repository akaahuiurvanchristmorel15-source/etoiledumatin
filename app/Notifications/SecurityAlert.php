<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class SecurityAlert extends Notification
{
    /**
     * Résumé des activités anormales relevées dans le journal de sécurité sur la période.
     *
     * @param  list<string>  $findings
     */
    public function __construct(public array $findings, public Carbon $since, public Carbon $until) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Alerte de sécurité — NSOROMMA')
            ->greeting('Activité inhabituelle détectée')
            ->line('Entre '.$this->since->format('d/m/Y H:i').' et '.$this->until->format('H:i').', le journal de sécurité a relevé :');

        foreach ($this->findings as $finding) {
            $message->line('• '.$finding);
        }

        return $message
            ->action('Ouvrir le journal de sécurité', route('admin.security.index'))
            ->line('Si une de ces actions ne vient pas de votre équipe, changez les mots de passe concernés et bloquez l\'adresse IP dans Cloudflare.');
    }
}
