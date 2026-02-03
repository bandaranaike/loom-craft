<?php

use Illuminate\Support\Facades\Schema;

it('creates core marketplace tables', function () {
    $tables = [
        'vendors',
        'products',
        'product_media',
        'carts',
        'cart_items',
        'orders',
        'order_items',
        'order_addresses',
        'shipments',
        'payments',
        'vendor_payouts',
        'disputes',
        'complaints',
        'product_reports',
        'suggestions',
    ];

    foreach ($tables as $table) {
        expect(Schema::hasTable($table))->toBeTrue();
    }
});

it('adds role column to users', function () {
    expect(Schema::hasColumn('users', 'role'))->toBeTrue();
});
