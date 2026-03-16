<?php

return [
    'base_currency' => env('COMMERCE_BASE_CURRENCY', 'LKR'),

    'commission_rate' => env('COMMERCE_COMMISSION_RATE', '100.00'),

    'paypal_currency' => env('COMMERCE_PAYPAL_CURRENCY', 'USD'),

    'exchange_rate_stale_hours' => (int) env('COMMERCE_EXCHANGE_RATE_STALE_HOURS', 24),

    'exchange_rate_source' => env('COMMERCE_EXCHANGE_RATE_SOURCE', 'open_er_api'),

    'exchange_rate_base_url' => env('COMMERCE_EXCHANGE_RATE_BASE_URL', 'https://open.er-api.com'),

    'exchange_rate_timeout_seconds' => (int) env('COMMERCE_EXCHANGE_RATE_TIMEOUT_SECONDS', 15),
];
