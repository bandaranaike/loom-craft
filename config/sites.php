<?php

return [
    'default' => env('APP_SITE', 'loomcraft'),

    'sites' => [
        'loomcraft' => [
            'key' => 'loomcraft',
            'name' => env('LOOMCRAFT_NAME', 'LoomCraft'),
            'display_name' => env('LOOMCRAFT_NAME', 'LoomCraft'),
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
            'label_logo' => 'brand/loomcraft-logo.png',
            'fulfillment_return_address' => [
                'name' => 'Loomcraft Pvt Ltd',
                'lines' => ['84\1, Weediyawaththa', 'Kundasale', 'Sri Lanka'],
                'phone' => "+94 712 512 512",
            ],
        ],

        'naturesnature' => [
            'key' => 'naturesnature',
            'name' => env('NATURESNATURE_NAME', "Nature's Nature"),
            'display_name' => env('NATURESNATURE_NAME', "Nature's Nature"),
            'domain' => env('NATURESNATURE_DOMAIN', 'naturesnature.store'),
            'theme' => 'naturesnature',
            'tagline' => 'Organic homemade foods',
            'description' => 'Organic homemade cookies, pantry treats, and curated food gifts prepared with warm, natural ingredients.',
            'marketplace_label' => 'Pure Flavors From Nature',
            'products_label' => 'Pantry',
            'dashboard_label' => 'Open Kitchen',
            'register_label' => 'Join the Kitchen',
            'vendor_label' => 'Maker',
            'vendor_plural_label' => 'Makers',
            'reviewer_label' => 'Customer',
            'hide_loom_features' => true,
            'label_logo' => 'brand/natures-nature-seal.png',
            'fulfillment_return_address' => [
                'name' => "Nature's Nature Fulfillment Center",
                'lines' => ['Nagolla', 'Ukuwela', 'Sri Lanka'],
                'phone' => "+94 75 135 5635",
            ],
        ],
    ],
];
