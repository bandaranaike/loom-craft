<?php

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

it('allows guests to add items to the cart', function () {
    $vendorUser = User::factory()->create(['role' => 'vendor']);
    $vendor = Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
    ]);

    $product = Product::factory()->for($vendor)->create([
        'status' => 'active',
        'selling_price' => '125.00',
        'discount_percentage' => '20.00',
    ]);

    $response = $this->post(route('cart.items.store'), [
        'product_id' => $product->id,
        'quantity' => 2,
        'currency' => 'LKR',
    ]);

    $response->assertRedirect(route('cart.show'));
    $response->assertCookie('loomcraft_guest_token');

    $cart = Cart::query()->firstOrFail();

    $this->assertDatabaseHas('cart_items', [
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'unit_price' => '100.00',
    ]);
});

it('shows the cart page for guests and queues a guest token cookie', function () {
    $response = $this->get(route('cart.show'));

    $response
        ->assertOk()
        ->assertCookie('loomcraft_guest_token')
        ->assertInertia(fn (Assert $page) => $page
            ->component('cart')
            ->where('cart.item_count', 0)
            ->where('cart.currency', 'LKR')
        );
});

it('shows a stock delay warning in the cart when quantity exceeds available pieces', function () {
    $vendorUser = User::factory()->create(['role' => 'vendor']);
    $vendor = Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
    ]);

    $product = Product::factory()->for($vendor)->create([
        'status' => 'active',
        'pieces_count' => 1,
        'production_time_days' => 14,
        'selling_price' => '180.00',
    ]);

    $cart = Cart::query()->create([
        'guest_token' => 'guest-token',
        'currency' => 'LKR',
    ]);

    $cart->items()->create([
        'product_id' => $product->id,
        'quantity' => 3,
        'unit_price' => '180.00',
    ]);

    $this->withCookie('loomcraft_guest_token', 'guest-token')
        ->get(route('cart.show'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('cart')
            ->where('cart.items.0.exceeds_available_stock', true)
            ->where('cart.items.0.available_quantity', 1)
            ->where('cart.items.0.production_time_days', 14)
            ->where(
                'cart.items.0.stock_delay_message',
                'This quantity is not currently in stock. Your order will require additional production time and is expected to take about 14 days.',
            )
        );
});
