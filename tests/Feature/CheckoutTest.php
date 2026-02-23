<?php

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

it('shows checkout for guests and preserves the guest token cookie', function () {
    $vendorUser = User::factory()->create(['role' => 'vendor']);
    $vendor = Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
    ]);

    $product = Product::factory()->for($vendor)->create([
        'status' => 'active',
        'selling_price' => '180.00',
    ]);

    $cart = Cart::query()->create([
        'guest_token' => 'guest-token',
        'currency' => 'USD',
    ]);

    $cart->items()->create([
        'product_id' => $product->id,
        'quantity' => 1,
        'unit_price' => '180.00',
    ]);

    $response = $this
        ->withCookie('loomcraft_guest_token', 'guest-token')
        ->get(route('checkout.show'));

    $response
        ->assertOk()
        ->assertCookie('loomcraft_guest_token')
        ->assertInertia(fn (Assert $page) => $page->component('checkout'));
});

it('creates an order from checkout and clears the cart', function () {
    $vendorUser = User::factory()->create(['role' => 'vendor']);
    $vendor = Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
    ]);

    $product = Product::factory()->for($vendor)->create([
        'status' => 'active',
        'selling_price' => '180.00',
    ]);

    $cart = Cart::query()->create([
        'guest_token' => 'guest-token',
        'currency' => 'USD',
    ]);

    $cart->items()->create([
        'product_id' => $product->id,
        'quantity' => 1,
        'unit_price' => '180.00',
    ]);

    $payload = [
        'guest_name' => 'Heritage Patron',
        'guest_email' => 'patron@example.com',
        'currency' => 'USD',
        'shipping_responsibility' => 'vendor',
        'payment_method' => 'stripe',
        'shipping_full_name' => 'Heritage Patron',
        'shipping_line1' => '1 Loom Street',
        'shipping_line2' => 'Suite 2',
        'shipping_city' => 'Kandy',
        'shipping_region' => 'Central',
        'shipping_postal_code' => '20000',
        'shipping_country_code' => 'LK',
        'shipping_phone' => '0770000000',
        'billing_full_name' => 'Heritage Patron',
        'billing_line1' => '1 Loom Street',
        'billing_line2' => null,
        'billing_city' => 'Kandy',
        'billing_region' => 'Central',
        'billing_postal_code' => '20000',
        'billing_country_code' => 'LK',
        'billing_phone' => '0770000000',
    ];

    $response = $this
        ->withCookie('loomcraft_guest_token', 'guest-token')
        ->post(route('checkout.store'), $payload);

    $order = Order::query()->firstOrFail();

    $response->assertRedirect(route('orders.confirmation', ['order' => $order->id]));

    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'status' => 'paid',
        'currency' => 'USD',
        'subtotal' => '180.00',
        'commission_total' => '12.60',
        'total' => '180.00',
        'shipping_responsibility' => 'vendor',
        'guest_email' => 'patron@example.com',
    ]);

    $this->assertDatabaseHas('order_items', [
        'order_id' => $order->id,
        'product_id' => $product->id,
        'vendor_id' => $vendor->id,
        'quantity' => 1,
        'unit_price' => '180.00',
        'commission_rate' => '7.00',
        'commission_amount' => '12.60',
        'line_total' => '180.00',
    ]);

    $this->assertDatabaseHas('order_addresses', [
        'order_id' => $order->id,
        'type' => 'shipping',
        'full_name' => 'Heritage Patron',
        'city' => 'Kandy',
    ]);

    $this->assertDatabaseHas('payments', [
        'order_id' => $order->id,
        'method' => 'stripe',
        'status' => 'paid',
        'amount' => '180.00',
        'currency' => 'USD',
    ]);

    $this->assertDatabaseMissing('cart_items', [
        'cart_id' => $cart->id,
    ]);
});

it('creates a PayPal order and stores pending checkout data in session', function () {
    config()->set('services.paypal.client_id', 'paypal-client');
    config()->set('services.paypal.client_secret', 'paypal-secret');
    config()->set('services.paypal.base_url', 'https://api-m.sandbox.paypal.com');

    Http::fake([
        'https://api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response([
            'access_token' => 'paypal-access-token',
        ]),
        'https://api-m.sandbox.paypal.com/v2/checkout/orders' => Http::response([
            'id' => 'PAYPAL-ORDER-1',
            'links' => [
                [
                    'rel' => 'approve',
                    'href' => 'https://www.sandbox.paypal.com/checkoutnow?token=PAYPAL-ORDER-1',
                ],
            ],
        ]),
    ]);

    $vendorUser = User::factory()->create(['role' => 'vendor']);
    $vendor = Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
    ]);

    $product = Product::factory()->for($vendor)->create([
        'status' => 'active',
        'selling_price' => '180.00',
    ]);

    Cart::query()->create([
        'guest_token' => 'guest-token',
        'currency' => 'USD',
    ])->items()->create([
        'product_id' => $product->id,
        'quantity' => 1,
        'unit_price' => '180.00',
    ]);

    $payload = [
        'guest_name' => 'Heritage Patron',
        'guest_email' => 'patron@example.com',
        'currency' => 'USD',
        'shipping_responsibility' => 'vendor',
        'payment_method' => 'paypal',
        'shipping_full_name' => 'Heritage Patron',
        'shipping_line1' => '1 Loom Street',
        'shipping_line2' => 'Suite 2',
        'shipping_city' => 'Kandy',
        'shipping_region' => 'Central',
        'shipping_postal_code' => '20000',
        'shipping_country_code' => 'LK',
        'shipping_phone' => '0770000000',
        'billing_full_name' => 'Heritage Patron',
        'billing_line1' => '1 Loom Street',
        'billing_line2' => null,
        'billing_city' => 'Kandy',
        'billing_region' => 'Central',
        'billing_postal_code' => '20000',
        'billing_country_code' => 'LK',
        'billing_phone' => '0770000000',
    ];

    $response = $this
        ->withCookie('loomcraft_guest_token', 'guest-token')
        ->postJson(route('checkout.paypal.create'), $payload);

    $response
        ->assertOk()
        ->assertJsonPath('order_id', 'PAYPAL-ORDER-1')
        ->assertJsonPath('approve_url', 'https://www.sandbox.paypal.com/checkoutnow?token=PAYPAL-ORDER-1');

    $response->assertSessionHas('checkout.paypal.pending.PAYPAL-ORDER-1.data.payment_method', 'paypal');
});

it('captures a PayPal order and creates the final order', function () {
    config()->set('services.paypal.client_id', 'paypal-client');
    config()->set('services.paypal.client_secret', 'paypal-secret');
    config()->set('services.paypal.base_url', 'https://api-m.sandbox.paypal.com');

    Http::fake([
        'https://api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response([
            'access_token' => 'paypal-access-token',
        ]),
        'https://api-m.sandbox.paypal.com/v2/checkout/orders/PAYPAL-ORDER-1/capture' => Http::response([
            'status' => 'COMPLETED',
            'purchase_units' => [
                [
                    'payments' => [
                        'captures' => [
                            [
                                'id' => 'PAYPAL-CAPTURE-1',
                            ],
                        ],
                    ],
                ],
            ],
        ]),
    ]);

    $vendorUser = User::factory()->create(['role' => 'vendor']);
    $vendor = Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
    ]);

    $product = Product::factory()->for($vendor)->create([
        'status' => 'active',
        'selling_price' => '180.00',
    ]);

    $cart = Cart::query()->create([
        'guest_token' => 'guest-token',
        'currency' => 'USD',
    ]);

    $cart->items()->create([
        'product_id' => $product->id,
        'quantity' => 1,
        'unit_price' => '180.00',
    ]);

    $payload = [
        'guest_name' => 'Heritage Patron',
        'guest_email' => 'patron@example.com',
        'currency' => 'USD',
        'shipping_responsibility' => 'vendor',
        'payment_method' => 'paypal',
        'shipping_full_name' => 'Heritage Patron',
        'shipping_line1' => '1 Loom Street',
        'shipping_line2' => 'Suite 2',
        'shipping_city' => 'Kandy',
        'shipping_region' => 'Central',
        'shipping_postal_code' => '20000',
        'shipping_country_code' => 'LK',
        'shipping_phone' => '0770000000',
        'billing_full_name' => 'Heritage Patron',
        'billing_line1' => '1 Loom Street',
        'billing_line2' => null,
        'billing_city' => 'Kandy',
        'billing_region' => 'Central',
        'billing_postal_code' => '20000',
        'billing_country_code' => 'LK',
        'billing_phone' => '0770000000',
    ];

    $response = $this
        ->withSession([
            'checkout.paypal.pending' => [
                'PAYPAL-ORDER-1' => [
                    'data' => $payload,
                    'guest_token' => 'guest-token',
                    'created_at' => now()->timestamp,
                ],
            ],
        ])
        ->get(route('checkout.paypal.approved', ['token' => 'PAYPAL-ORDER-1']));

    $order = Order::query()->firstOrFail();

    $response->assertRedirect(route('orders.confirmation', ['order' => $order->id]));

    $this->assertDatabaseHas('payments', [
        'order_id' => $order->id,
        'method' => 'paypal',
        'status' => 'paid',
        'provider_reference' => 'PAYPAL-CAPTURE-1',
    ]);

    $this->assertDatabaseMissing('cart_items', [
        'cart_id' => $cart->id,
    ]);
});
