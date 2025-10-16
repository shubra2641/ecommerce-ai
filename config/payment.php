<?php

return [
    'default_gateway' => env('PAYMENT_DEFAULT_GATEWAY', 'paypal'),

    'gateways' => [
        'paypal' => [
            'client_id' => env('PAYPAL_CLIENT_ID'),
            'client_secret' => env('PAYPAL_CLIENT_SECRET'),
            'test_mode' => env('PAYPAL_TEST_MODE', true),
        ],

        'stripe' => [
            'publishable_key' => env('STRIPE_PUBLISHABLE_KEY'),
            'secret_key' => env('STRIPE_SECRET_KEY'),
            'test_mode' => env('STRIPE_TEST_MODE', true),
        ],

        'tap' => [
            'publishable_key' => env('TAP_PUBLISHABLE_KEY'),
            'secret_key' => env('TAP_SECRET_KEY'),
            'test_mode' => env('TAP_TEST_MODE', true),
        ],
    ],

    'currencies' => [
        'USD' => 'US Dollar',
        'EUR' => 'Euro',
        'GBP' => 'British Pound',
        'AED' => 'UAE Dirham',
        'SAR' => 'Saudi Riyal',
        'EGP' => 'Egyptian Pound',
    ],

    'webhook_secret' => env('PAYMENT_WEBHOOK_SECRET'),
];
