<?php

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Vendor;
use Inertia\Testing\AssertableInertia as Assert;

test('public product pages use the highest available discount', function () {
    $vendor = Vendor::factory()->create([
        'status' => 'approved',
        'display_name' => 'Heritage Loom Atelier',
    ]);
    $category = ProductCategory::factory()->create([
        'discount_percentage' => '15.00',
    ]);
    $product = Product::factory()->for($vendor)->create([
        'status' => 'active',
        'selling_price' => '200.00',
        'discount_percentage' => '10.00',
        'name' => 'Discounted Heritage Runner',
    ]);
    $product->categories()->sync([$category->id]);

    $this->get(route('products.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('products/index')
            ->where('products.0.id', $product->id)
            ->where('products.0.original_price', '200.00')
            ->where('products.0.selling_price', '170.00')
            ->where('products.0.effective_discount_percentage', '15.00')
            ->where('products.0.has_discount', true)
        );

    $this->get(route('products.show', $product))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('products/show')
            ->where('product.id', $product->id)
            ->where('product.original_price', '200.00')
            ->where('product.selling_price', '170.00')
            ->where('product.effective_discount_percentage', '15.00')
            ->where('product.has_discount', true)
        );
});

test('checkout persists discounted prices using the highest applicable discount', function () {
    $vendor = Vendor::factory()->create([
        'status' => 'approved',
    ]);
    $category = ProductCategory::factory()->create([
        'discount_percentage' => '20.00',
    ]);
    $product = Product::factory()->for($vendor)->create([
        'status' => 'active',
        'selling_price' => '180.00',
        'discount_percentage' => '10.00',
    ]);
    $product->categories()->sync([$category->id]);

    $cart = Cart::query()->create([
        'guest_token' => 'guest-token',
        'currency' => 'USD',
    ]);

    $cart->items()->create([
        'product_id' => $product->id,
        'quantity' => 1,
        'unit_price' => '144.00',
    ]);

    $payload = [
        'guest_name' => 'Discount Buyer',
        'guest_email' => 'buyer@example.com',
        'currency' => 'USD',
        'shipping_responsibility' => 'vendor',
        'payment_method' => 'stripe',
        'shipping_full_name' => 'Discount Buyer',
        'shipping_line1' => '1 Loom Street',
        'shipping_line2' => null,
        'shipping_city' => 'Kandy',
        'shipping_region' => 'Central',
        'shipping_postal_code' => '20000',
        'shipping_country_code' => 'LK',
        'shipping_phone' => '0770000000',
        'billing_full_name' => 'Discount Buyer',
        'billing_line1' => '1 Loom Street',
        'billing_line2' => null,
        'billing_city' => 'Kandy',
        'billing_region' => 'Central',
        'billing_postal_code' => '20000',
        'billing_country_code' => 'LK',
        'billing_phone' => '0770000000',
    ];

    $this->withCookie('loomcraft_guest_token', 'guest-token')
        ->post(route('checkout.store'), $payload)
        ->assertRedirect();

    $order = Order::query()->firstOrFail();

    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'subtotal' => '144.00',
        'commission_total' => '144.00',
        'total' => '144.00',
    ]);

    $this->assertDatabaseHas('order_items', [
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'unit_price' => '144.00',
        'commission_rate' => '100.00',
        'commission_amount' => '144.00',
        'line_total' => '144.00',
    ]);
});
