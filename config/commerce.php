<?php

return [
    'base_currency' => env('COMMERCE_BASE_CURRENCY', 'LKR'),

    'default_country_code' => env('COMMERCE_DEFAULT_COUNTRY_CODE', 'LK'),

    'commission_rate' => env('COMMERCE_COMMISSION_RATE', '100.00'),

    'paypal_currency' => env('COMMERCE_PAYPAL_CURRENCY', 'USD'),

    'exchange_rate_stale_hours' => (int) env('COMMERCE_EXCHANGE_RATE_STALE_HOURS', 24),

    'exchange_rate_source' => env('COMMERCE_EXCHANGE_RATE_SOURCE', 'open_er_api'),

    'exchange_rate_base_url' => env('COMMERCE_EXCHANGE_RATE_BASE_URL', 'https://open.er-api.com'),

    'exchange_rate_timeout_seconds' => (int) env('COMMERCE_EXCHANGE_RATE_TIMEOUT_SECONDS', 15),

    'production_time_setup_days' => (int) env('COMMERCE_PRODUCTION_TIME_SETUP_DAYS', 2),

    'production_time_buffer_rate' => (float) env('COMMERCE_PRODUCTION_TIME_BUFFER_RATE', 0.10),

    'production_time_default_weaving_days' => (float) env('COMMERCE_PRODUCTION_TIME_DEFAULT_WEAVING_DAYS', 1),

    'production_time_large_cart_threshold' => (int) env('COMMERCE_PRODUCTION_TIME_LARGE_CART_THRESHOLD', 6),
];
