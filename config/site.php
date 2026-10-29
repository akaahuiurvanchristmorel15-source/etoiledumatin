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
        'legal_company_name' => [
            'group' => 'Informations légales',
            'label' => "Nom de l'éditeur",
            'help' => 'Raison sociale de la société qui exploite le site.',
            'default' => 'Éditions NSOROMMA',
            'rules' => ['nullable', 'string', 'max:255'],
        ],
        'legal_form' => [
            'group' => 'Informations légales',
            'label' => 'Forme juridique et capital',
            'help' => 'Ex. SARL au capital de 1 000 000 FCFA.',
            'default' => '',
            'rules' => ['nullable', 'string', 'max:255'],
        ],
        'legal_address' => [
            'group' => 'Informations légales',
            'label' => 'Adresse du siège',
            'help' => "Adresse postale complète en Côte d'Ivoire.",
            'default' => '',
            'rules' => ['nullable', 'string', 'max:255'],
        ],
        'legal_rccm' => [
            'group' => 'Informations légales',
            'label' => 'N° RCCM',
            'help' => 'Registre du commerce et du crédit mobilier.',
            'default' => '',
            'rules' => ['nullable', 'string', 'max:255'],
        ],
        'legal_tax_id' => [
            'group' => 'Informations légales',
            'label' => 'N° de compte contribuable (NCC)',
            'default' => '',
            'rules' => ['nullable', 'string', 'max:255'],
        ],
        'legal_publication_director' => [
            'group' => 'Informations légales',
            'label' => 'Directeur de la publication',
            'help' => 'Personne responsable du contenu du site.',
            'default' => '',
            'rules' => ['nullable', 'string', 'max:255'],
        ],
        'legal_artci' => [
            'group' => 'Informations légales',
            'label' => 'Déclaration ARTCI',
            'help' => "Numéro du récépissé de déclaration du traitement des données personnelles auprès de l'ARTCI.",
            'default' => '',
            'rules' => ['nullable', 'string', 'max:255'],
        ],
        'legal_host' => [
            'group' => 'Informations légales',
            'label' => 'Hébergeur',
            'help' => "Nom et adresse de l'hébergeur du site.",
            'default' => 'Namecheap, Inc., Phoenix (Arizona), États-Unis — www.namecheap.com',
            'rules' => ['nullable', 'string', 'max:255'],
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
