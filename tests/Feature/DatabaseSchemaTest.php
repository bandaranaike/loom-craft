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
        'invoices',
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

it('adds fulfillment identifier and parcel columns', function () {
    expect(Schema::hasColumns('orders', ['public_id', 'order_number']))->toBeTrue()
        ->and(Schema::hasColumns('shipments', [
            'shipment_number',
            'service_level',
            'package_count',
            'parcel_weight',
            'weight_unit',
            'parcel_length',
            'parcel_width',
            'parcel_height',
            'parcel_dimension_unit',
        ]))->toBeTrue()
        ->and(Schema::hasColumns('invoices', [
            'order_id',
            'invoice_number',
            'status',
            'currency',
            'subtotal',
            'commission_total',
            'total',
            'issued_at',
        ]))->toBeTrue();
});
