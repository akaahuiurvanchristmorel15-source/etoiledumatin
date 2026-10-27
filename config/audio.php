<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Langues d'écoute
    |--------------------------------------------------------------------------
    |
    | Chaque partie d'un livre peut recevoir une piste audio par langue.
    | Ajouter une langue ici suffit pour qu'elle apparaisse dans l'administration
    | et dans le lecteur de la bibliothèque.
    |
    */
    'languages' => [
        'fr' => 'Français',
        'en' => 'Anglais',
        'baoule' => 'Baoulé',
        'malinke' => 'Malinké',
        'bete' => 'Bété',
        'dida' => 'Dida',
        'djimini' => 'Djimini',
        'godie' => 'Godié',
    ],

    /*
    |--------------------------------------------------------------------------
    | Formules d'abonnement (durée en jours => libellé)
    |--------------------------------------------------------------------------
    |
    | Le prix de chaque formule est fixé livre par livre dans l'administration.
    | Une formule sans prix n'est pas proposée à la vente.
    |
    */
    'plans' => [
        7 => '1 semaine',
        30 => '1 mois',
        90 => '3 mois',
        365 => '1 an',
    ],

    'disk' => 'local',

    'directory' => 'audio-tracks',
];
