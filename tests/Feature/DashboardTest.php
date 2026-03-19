<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('dashboard surfaces flash status messages', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->withSession([
        'status' => 'Your vendor application has been submitted for review.',
    ])->get(route('dashboard'));

    $response
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('dashboard')
            ->where('status', 'Your vendor application has been submitted for review.')
        );
});

test('dashboard includes order histories for authenticated users', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $vendorUser = User::factory()->create(['role' => 'vendor']);
    $vendor = Vendor::factory()->for($vendorUser)->create(['status' => 'approved']);

    $product = Product::factory()->for($vendor)->create([
        'status' => 'active',
        'selling_price' => '245.00',
    ]);

    $order = Order::query()->create([
        'user_id' => $customer->id,
        'status' => 'paid',
        'currency' => 'USD',
        'subtotal' => '245.00',
        'commission_total' => '17.15',
        'total' => '245.00',
        'shipping_responsibility' => 'platform',
        'placed_at' => now(),
    ]);

    $order->items()->create([
        'product_id' => $product->id,
        'vendor_id' => $vendor->id,
        'quantity' => 1,
        'unit_price' => '245.00',
        'commission_rate' => '7.00',
        'commission_amount' => '17.15',
        'line_total' => '245.00',
    ]);

    $order->addresses()->create([
        'type' => 'shipping',
        'full_name' => 'Jane Doe',
        'line1' => '123 Craft Street',
        'line2' => null,
        'city' => 'Colombo',
        'region' => null,
        'postal_code' => '10000',
        'country_code' => 'LK',
        'phone' => '0771234567',
    ]);

    $order->payment()->create([
        'method' => 'paypal',
        'status' => 'paid',
        'amount' => '245.00',
        'currency' => 'USD',
        'provider_reference' => 'PAYPAL-TEST',
    ]);

    $this->actingAs($customer)
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('dashboard')
            ->has('order_histories', 1)
            ->has(
                'order_histories.0.items',
                1,
                fn (Assert $item) => $item
                    ->where('product_name', $product->name)
                    ->where('vendor_name', $vendor->display_name)
                    ->etc(),
            )
            ->where('order_histories.0.id', $order->id)
            ->where('order_histories.0.status', 'paid')
            ->where('order_histories.0.payment_status', 'paid')
        );
});
