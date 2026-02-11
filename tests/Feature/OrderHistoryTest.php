<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

it('shows order history for authenticated customers', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $vendorUser = User::factory()->create(['role' => 'vendor']);
    $vendor = Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
    ]);

    $product = Product::factory()->for($vendor)->create([
        'status' => 'active',
        'selling_price' => '120.00',
    ]);

    $order = Order::query()->create([
        'user_id' => $customer->id,
        'status' => 'paid',
        'currency' => 'USD',
        'subtotal' => '120.00',
        'commission_total' => '8.40',
        'total' => '120.00',
        'shipping_responsibility' => 'vendor',
        'placed_at' => now(),
    ]);

    $order->items()->create([
        'product_id' => $product->id,
        'vendor_id' => $vendor->id,
        'quantity' => 1,
        'unit_price' => '120.00',
        'commission_rate' => '7.00',
        'commission_amount' => '8.40',
        'line_total' => '120.00',
    ]);

    $this->actingAs($customer)
        ->get(route('orders.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('orders/index')
            ->has('orders', 1)
        );
});
