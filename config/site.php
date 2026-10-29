<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Contenus du site modifiables dans l'administration
    |--------------------------------------------------------------------------
    |
    | Valeur par défaut, libellé et règles de chaque contenu. Les valeurs saisies
    | sont enregistrées dans la table des paramètres (clé « content.<nom> »).
    |
    */
    'content' => [
        'announcement_text' => [
            'group' => 'Bandeau d\'annonce',
            'label' => 'Texte du bandeau',
            'help' => 'Affiché en haut de toutes les pages. Laissez vide pour masquer le bandeau.',
            'default' => '',
            'rules' => ['nullable', 'string', 'max:200'],
        ],
        'announcement_link' => [
            'group' => 'Bandeau d\'annonce',
            'label' => 'Lien du bandeau',
            'help' => 'Adresse ouverte au clic sur le bandeau (facultatif).',
            'default' => '',
            'rules' => ['nullable', 'url:http,https', 'max:255'],
        ],
        'home_collections_title' => [
            'group' => 'Page d\'accueil',
            'label' => 'Titre des collections',
            'default' => 'Nos collections',
            'rules' => ['required', 'string', 'max:80'],
        ],
        'home_news_title' => [
            'group' => 'Page d\'accueil',
            'label' => 'Titre des actualités',
            'default' => 'Dernières actualités',
            'rules' => ['required', 'string', 'max:80'],
        ],
        'support_whatsapp' => [
            'group' => 'Contact',
            'label' => 'Numéro WhatsApp d\'assistance',
            'help' => 'Format international sans espace ni « + », ex. 2250700000001.',
            'default' => '2250700000001',
            'rules' => ['nullable', 'regex:/^\d{8,15}$/'],
        ],
        'contact_email' => [
            'group' => 'Contact',
            'label' => 'E-mail de contact',
            'default' => '',
            'rules' => ['nullable', 'email', 'max:150'],
        ],
        'contact_phone' => [
            'group' => 'Contact',
            'label' => 'Téléphone',
            'default' => '',
            'rules' => ['nullable', 'string', 'max:30'],
        ],
        'facebook_url' => [
            'group' => 'Réseaux sociaux',
            'label' => 'Facebook',
            'default' => '',
            'rules' => ['nullable', 'url:https', 'max:255'],
        ],
        'instagram_url' => [
            'group' => 'Réseaux sociaux',
            'label' => 'Instagram',
            'default' => '',
            'rules' => ['nullable', 'url:https', 'max:255'],
        ],
        'tiktok_url' => [
            'group' => 'Réseaux sociaux',
            'label' => 'TikTok',
            'default' => '',
            'rules' => ['nullable', 'url:https', 'max:255'],
        ],
        'youtube_url' => [
            'group' => 'Réseaux sociaux',
            'label' => 'YouTube',
            'default' => '',
            'rules' => ['nullable', 'url:https', 'max:255'],
        ],
    ],
];
