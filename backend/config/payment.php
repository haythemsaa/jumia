<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Payment Gateway
    |--------------------------------------------------------------------------
    |
    | This option controls the default payment gateway that will be used.
    | You may set this to any of the gateways defined in the "gateways" array.
    |
    */

    'default' => env('PAYMENT_GATEWAY', 'konnect'),

    /*
    |--------------------------------------------------------------------------
    | Payment Gateways
    |--------------------------------------------------------------------------
    |
    | Configuration for each payment gateway.
    |
    */

    'gateways' => [
        'edinar' => [
            'enabled' => env('EDINAR_ENABLED', false),
            'merchant_id' => env('EDINAR_MERCHANT_ID'),
            'terminal_id' => env('EDINAR_TERMINAL_ID'),
            'secret_key' => env('EDINAR_SECRET_KEY'),
            'test_mode' => env('EDINAR_TEST_MODE', true),
        ],

        'konnect' => [
            'enabled' => env('KONNECT_ENABLED', true),
            'api_key' => env('KONNECT_API_KEY'),
            'wallet_id' => env('KONNECT_WALLET_ID'),
            'test_mode' => env('KONNECT_TEST_MODE', true),
        ],

        'd17' => [
            'enabled' => env('D17_ENABLED', false),
            'api_key' => env('D17_API_KEY'),
            'merchant_id' => env('D17_MERCHANT_ID'),
            'test_mode' => env('D17_TEST_MODE', true),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Settings
    |--------------------------------------------------------------------------
    */

    'currency' => env('PAYMENT_CURRENCY', 'TND'),

    'cash_on_delivery_enabled' => env('CASH_ON_DELIVERY_ENABLED', true),

    'max_refund_days' => env('MAX_REFUND_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | Commission
    |--------------------------------------------------------------------------
    |
    | Platform commission percentage (e.g., 12 for 12%)
    |
    */

    'commission_rate' => env('COMMISSION_RATE', 12),
];
