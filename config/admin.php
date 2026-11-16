<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Verrouillage de l'administration
    |--------------------------------------------------------------------------
    |
    | idle_minutes : un membre de l'équipe inactif est déconnecté de l'administration.
    | allowed_ips : si renseignée (adresses séparées par des virgules), seule cette
    | liste peut ouvrir l'administration ; vide, toute adresse est acceptée.
    |
    */

    'idle_minutes' => (int) env('ADMIN_IDLE_MINUTES', 30),

    'allowed_ips' => array_values(array_filter(array_map('trim', explode(',', (string) env('ADMIN_ALLOWED_IPS', ''))))),

    /*
    |--------------------------------------------------------------------------
    | Alertes de sécurité
    |--------------------------------------------------------------------------
    |
    | Les super administrateurs reçoivent un e-mail quand le journal de sécurité
    | montre une activité anormale (voir la commande security:alert).
    |
    */

    'alerts_enabled' => (bool) env('SECURITY_ALERTS', true),

];
