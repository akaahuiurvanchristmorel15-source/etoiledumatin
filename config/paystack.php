<?php

return [
    'public_key' => env('PAYSTACK_PUBLIC_KEY', ''),
    'secret_key' => env('PAYSTACK_SECRET_KEY', ''),
    'payment_url' => env('PAYSTACK_PAYMENT_URL', 'https://api.paystack.co'),
    'merchant_email' => env('PAYSTACK_MERCHANT_EMAIL', 'contact@letoiledumatin.ci'),
    'sandbox_mode' => env('PAYSTACK_SANDBOX_MODE', true),
    'currency' => env('PAYSTACK_CURRENCY', 'XOF'),

    // Certificats racine pour PHP sous Windows (développement local) ; inutile sur le serveur
    'ca_bundle' => env('PAYSTACK_CA_BUNDLE'),
];
