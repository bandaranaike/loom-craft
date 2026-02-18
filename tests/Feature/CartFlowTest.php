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
    ]);

    $response = $this->post(route('cart.items.store'), [
        'product_id' => $product->id,
        'quantity' => 2,
        'currency' => 'USD',
    ]);

    $response->assertRedirect(route('cart.show'));
    $response->assertCookie('loomcraft_guest_token');

    $cart = Cart::query()->firstOrFail();

    $this->assertDatabaseHas('cart_items', [
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'unit_price' => '125.00',
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
        );
});
