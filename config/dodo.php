<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Dodo Payments API
    |--------------------------------------------------------------------------
    |
    | Use test mode until live product IDs and credentials are configured.
    | Product IDs are server-owned — never accept them from the browser.
    |
    */

    'api_key' => env('DODO_PAYMENTS_API_KEY', ''),

    'webhook_secret' => env('DODO_PAYMENTS_WEBHOOK_SECRET', ''),

    'mode' => env('DODO_PAYMENTS_MODE', 'test'), // test|live

    'base_url' => env(
        'DODO_PAYMENTS_BASE_URL',
        env('DODO_PAYMENTS_MODE', 'test') === 'live'
            ? 'https://live.dodopayments.com'
            : 'https://test.dodopayments.com'
    ),

    'return_url' => env('DODO_PAYMENTS_RETURN_URL'),

    'cancel_url' => env('DODO_PAYMENTS_CANCEL_URL'),

    /*
    |--------------------------------------------------------------------------
    | Plan tiers (guest capacity)
    |--------------------------------------------------------------------------
    */

    'tiers' => [
        'basic' => [
            'guest_limit' => 100,
            'sort' => 1,
            'highlighted' => false,
        ],
        'plus' => [
            'guest_limit' => 200,
            'sort' => 2,
            'highlighted' => true,
        ],
        'premium' => [
            'guest_limit' => 300,
            'sort' => 3,
            'highlighted' => false,
        ],
        'deluxe' => [
            'guest_limit' => null, // unlimited
            'sort' => 4,
            'highlighted' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Regional catalogs
    |--------------------------------------------------------------------------
    |
    | first_world → EUR, third_world → BAM
    | Replace placeholder product IDs after creating products in Dodo.
    |
    */

    'regions' => [
        'first_world' => [
            'currency' => 'EUR',
            'prices' => [
                'basic' => 80,
                'plus' => 160,
                'premium' => 240,
                'deluxe' => 320,
            ],
        ],
        'third_world' => [
            'currency' => 'BAM',
            'prices' => [
                'basic' => 80,
                'plus' => 160,
                'premium' => 240,
                'deluxe' => 320,
            ],
        ],
    ],

    'products' => [
        'test' => [
            'first_world' => [
                'basic' => env('DODO_PRODUCT_TEST_FW_BASIC', 'pdt_test_fw_basic'),
                'plus' => env('DODO_PRODUCT_TEST_FW_PLUS', 'pdt_test_fw_plus'),
                'premium' => env('DODO_PRODUCT_TEST_FW_PREMIUM', 'pdt_test_fw_premium'),
                'deluxe' => env('DODO_PRODUCT_TEST_FW_DELUXE', 'pdt_test_fw_deluxe'),
            ],
            'third_world' => [
                'basic' => env('DODO_PRODUCT_TEST_TW_BASIC', 'pdt_test_tw_basic'),
                'plus' => env('DODO_PRODUCT_TEST_TW_PLUS', 'pdt_test_tw_plus'),
                'premium' => env('DODO_PRODUCT_TEST_TW_PREMIUM', 'pdt_test_tw_premium'),
                'deluxe' => env('DODO_PRODUCT_TEST_TW_DELUXE', 'pdt_test_tw_deluxe'),
            ],
        ],
        'live' => [
            'first_world' => [
                'basic' => env('DODO_PRODUCT_LIVE_FW_BASIC', 'pdt_live_fw_basic'),
                'plus' => env('DODO_PRODUCT_LIVE_FW_PLUS', 'pdt_live_fw_plus'),
                'premium' => env('DODO_PRODUCT_LIVE_FW_PREMIUM', 'pdt_live_fw_premium'),
                'deluxe' => env('DODO_PRODUCT_LIVE_FW_DELUXE', 'pdt_live_fw_deluxe'),
            ],
            'third_world' => [
                'basic' => env('DODO_PRODUCT_LIVE_TW_BASIC', 'pdt_live_tw_basic'),
                'plus' => env('DODO_PRODUCT_LIVE_TW_PLUS', 'pdt_live_tw_plus'),
                'premium' => env('DODO_PRODUCT_LIVE_TW_PREMIUM', 'pdt_live_tw_premium'),
                'deluxe' => env('DODO_PRODUCT_LIVE_TW_DELUXE', 'pdt_live_tw_deluxe'),
            ],
        ],
    ],

];
