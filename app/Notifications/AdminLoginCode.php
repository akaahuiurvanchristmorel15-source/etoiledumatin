<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminLoginCode extends Notification
{
    /**
     * Code de connexion à usage unique, envoyé immédiatement (pas de file d'attente : le membre attend devant l'écran).
     */
    public function __construct(public string $code, public int $validForMinutes) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Votre code de connexion — NSOROMMA')
            ->greeting('Bonjour')
            ->line('Voici votre code pour terminer la connexion à l\'administration NSOROMMA :')
            ->line('**'.rtrim(chunk_split($this->code, 3, ' ')).'**')
            ->line("Ce code expire dans {$this->validForMinutes} minutes.")
            ->line('Si vous n\'êtes pas à l\'origine de cette connexion, changez immédiatement votre mot de passe : quelqu\'un le connaît.');
    }
}
