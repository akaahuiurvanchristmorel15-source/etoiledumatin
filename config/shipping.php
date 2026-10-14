<?php

return [
    'zones' => [
        'abidjan' => [
            'name' => 'Abidjan (Coursier / Point Relais)',
            'price' => (int) env('SHIPPING_PRICE_ABIDJAN', 1500),
            'delay' => '24h à 48h',
            'description' => 'Livraison à domicile ou en point relais dans tout le district d\'Abidjan.',
        ],
        'interior' => [
            'name' => 'Intérieur de la Côte d\'Ivoire',
            'price' => (int) env('SHIPPING_PRICE_INTERIOR', 3000),
            'delay' => '48h à 72h',
            'description' => 'Expédition vers Bouaké, Yamoussoukro, San Pedro, Korhogo, Daloa, etc.',
        ],
        'uemoa' => [
            'name' => 'Zone UEMOA / Sous-région',
            'price' => (int) env('SHIPPING_PRICE_UEMOA', 7500),
            'delay' => '3 à 6 jours ouvrés',
            'description' => 'Sénégal, Mali, Burkina Faso, Bénin, Togo, Niger, Guinée-Bissau.',
        ],
        'international' => [
            'name' => 'International & Diaspora (Europe / Amérique / Autres)',
            'price' => (int) env('SHIPPING_PRICE_INTERNATIONAL', 15000),
            'delay' => '7 à 12 jours ouvrés',
            'description' => 'Envoi postal sécurisé / Colissimo international / DHL.',
        ],
    ],
];
