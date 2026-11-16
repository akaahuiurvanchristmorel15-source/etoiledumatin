<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Partage entre origines (CORS)
    |--------------------------------------------------------------------------
    |
    | Le site n'expose aucune API à d'autres domaines : aucune adresse n'envoie
    | d'en-tête CORS, donc un site tiers ne peut ni lire les réponses ni envoyer
    | de requête avec les cookies de l'utilisateur. Si une API publique est ajoutée,
    | listez ses chemins dans « paths » et les domaines autorisés dans « allowed_origins »
    | (jamais « * » avec des cookies).
    |
    */

    'paths' => [],

    'allowed_methods' => ['GET', 'POST'],

    'allowed_origins' => [rtrim((string) env('APP_URL', 'http://localhost'), '/')],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Content-Type', 'X-Requested-With', 'X-CSRF-TOKEN', 'Accept'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
