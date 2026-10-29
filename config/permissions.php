<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Permissions du back-office
    |--------------------------------------------------------------------------
    |
    | Chaque permission est une autorisation (Gate) du même nom, attribuable aux
    | rôles depuis Paramètres › Rôles et permissions. Le super administrateur
    | les possède toutes. Le tableau de bord et « Mon compte » restent ouverts
    | à tout membre de l'équipe.
    |
    */
    'manage-catalog' => [
        'label' => 'Livres et catégories',
        'description' => 'Ajouter, modifier et retirer des livres, leurs parties audio et les catégories.',
    ],
    'manage-stock' => [
        'label' => 'Stock',
        'description' => 'Consulter et ajuster le stock des livres papier.',
    ],
    'manage-news' => [
        'label' => 'Actualités',
        'description' => 'Publier et modifier les actualités, événements et sorties.',
    ],
    'manage-reviews' => [
        'label' => 'Avis clients',
        'description' => 'Approuver, masquer et supprimer les avis des lecteurs.',
    ],
    'manage-orders' => [
        'label' => 'Commandes et expéditions',
        'description' => 'Voir les commandes, exporter les ventes, suivre les livraisons et recevoir les alertes de commande.',
    ],
    'manage-store' => [
        'label' => 'Boutique',
        'description' => 'Frais de livraison et contenus du site (bandeau, contacts, informations légales).',
    ],
    'manage-team' => [
        'label' => 'Équipe et rôles',
        'description' => 'Ajouter des membres, leur attribuer un rôle et gérer les rôles et leurs permissions.',
    ],
];
