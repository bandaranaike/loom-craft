<?php

return [
    'default' => env('APP_SITE', 'loomcraft'),

    'sites' => [
        'loomcraft' => [
            'key' => 'loomcraft',
            'name' => 'LoomCraft',
            'display_name' => 'LoomCraft',
            'domain' => env('LOOMCRAFT_DOMAIN', 'loomcraft.work'),
            'theme' => 'loomcraft',
            'tagline' => 'Heritage woven luxury',
            'description' => 'Handwoven Sri Lankan textiles, curated artisan pieces, and collectible home decor from verified LoomCraft vendors.',
            'marketplace_label' => 'Heritage Marketplace',
            'products_label' => 'Products',
            'dashboard_label' => 'Enter Atelier',
            'register_label' => 'Become a Patron',
            'vendor_label' => 'Vendor',
            'vendor_plural_label' => 'Vendors',
            'reviewer_label' => 'Collector',
            'hide_loom_features' => false,
        ],

        'naturesnature' => [
            'key' => 'naturesnature',
            'name' => "Nature's Nature",
            'display_name' => "Nature's Nature",
            'domain' => env('NATURESNATURE_DOMAIN', 'naturesnature.com'),
            'theme' => 'naturesnature',
            'tagline' => 'Organic homemade foods',
            'description' => 'Organic homemade cookies, pantry treats, and curated food gifts prepared with warm, natural ingredients.',
            'marketplace_label' => 'Homemade Food Market',
            'products_label' => 'Pantry',
            'dashboard_label' => 'Open Kitchen',
            'register_label' => 'Join the Kitchen',
            'vendor_label' => 'Maker',
            'vendor_plural_label' => 'Makers',
            'reviewer_label' => 'Customer',
            'hide_loom_features' => true,
        ],
    ],
];
