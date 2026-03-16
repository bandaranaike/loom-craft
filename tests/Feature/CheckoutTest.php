<?php

use App\Models\Cart;
use App\Models\ExchangeRate;
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
        'currency' => 'LKR',
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

it('renders the csrf token meta tag on checkout', function () {
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
        'currency' => 'LKR',
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
        ->assertSee('meta name="csrf-token" content="', false);
});

it('shows stock delay warnings during checkout when quantity exceeds available pieces', function () {
    $vendorUser = User::factory()->create(['role' => 'vendor']);
    $vendor = Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
    ]);

    $product = Product::factory()->for($vendor)->create([
        'status' => 'active',
        'pieces_count' => 1,
        'production_time_days' => 10,
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
        ->get(route('checkout.show'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('checkout')
            ->where('cart.items.0.exceeds_available_stock', true)
            ->where('cart.items.0.available_quantity', 1)
            ->where('cart.items.0.production_time_days', 10)
            ->where(
                'cart.items.0.stock_delay_message',
                'This quantity is not currently in stock. Your order will require additional production time and is expected to take about 10 days.',
            )
        );
});

it('creates an order from checkout and clears the cart', function () {
    $commissionRate = (string) config('commerce.commission_rate');
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
        'currency' => 'LKR',
    ]);

    $cart->items()->create([
        'product_id' => $product->id,
        'quantity' => 1,
        'unit_price' => '180.00',
    ]);

    $payload = [
        'guest_name' => 'Heritage Patron',
        'guest_email' => 'patron@example.com',
        'currency' => 'LKR',
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

    $expectedCommissionAmount = number_format(180 * ((float) $commissionRate / 100), 2, '.', '');

    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'status' => 'paid',
        'currency' => 'LKR',
        'subtotal' => '180.00',
        'commission_total' => $expectedCommissionAmount,
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
        'commission_rate' => $commissionRate,
        'commission_amount' => $expectedCommissionAmount,
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
        'currency' => 'LKR',
        'original_amount' => '180.00',
        'original_currency' => 'LKR',
    ]);

    $this->assertDatabaseMissing('cart_items', [
        'cart_id' => $cart->id,
    ]);
});

it('still places an order when requested quantity exceeds available pieces', function () {
    $commissionRate = (string) config('commerce.commission_rate');
    $vendorUser = User::factory()->create(['role' => 'vendor']);
    $vendor = Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
    ]);

    $product = Product::factory()->for($vendor)->create([
        'status' => 'active',
        'pieces_count' => 1,
        'production_time_days' => 12,
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

    $payload = [
        'guest_name' => 'Heritage Patron',
        'guest_email' => 'patron@example.com',
        'currency' => 'LKR',
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
    $expectedCommissionAmount = number_format(540 * ((float) $commissionRate / 100), 2, '.', '');

    $response->assertRedirect(route('orders.confirmation', ['order' => $order->id]));

    $this->assertDatabaseHas('order_items', [
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 3,
        'unit_price' => '180.00',
        'commission_rate' => $commissionRate,
        'commission_amount' => $expectedCommissionAmount,
        'line_total' => '540.00',
    ]);
});

it('creates a PayPal order and stores pending checkout data in session', function () {
    config()->set('services.paypal.client_id', 'paypal-client');
    config()->set('services.paypal.client_secret', 'paypal-secret');
    config()->set('services.paypal.base_url', 'https://api-m.sandbox.paypal.com');

    ExchangeRate::factory()->create([
        'rate' => '0.00333333',
        'fetched_at' => now()->subHour(),
    ]);

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
        'currency' => 'LKR',
    ])->items()->create([
        'product_id' => $product->id,
        'quantity' => 1,
        'unit_price' => '180.00',
    ]);

    $payload = [
        'guest_name' => 'Heritage Patron',
        'guest_email' => 'patron@example.com',
        'currency' => 'LKR',
        'shipping_responsibility' => 'vendor',
        'payment_method' => 'paypal',
        'paypal_conversion_confirmed' => true,
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
    $response->assertSessionHas('checkout.paypal.pending.PAYPAL-ORDER-1.quote.original_amount', '180.00');
    $response->assertSessionHas('checkout.paypal.pending.PAYPAL-ORDER-1.quote.converted_amount', '0.60');
    $response->assertSessionHas('checkout.paypal.pending.PAYPAL-ORDER-1.quote.converted_currency', 'USD');

    Http::assertSent(function (\Illuminate\Http\Client\Request $request): bool {
        return $request->url() === 'https://api-m.sandbox.paypal.com/v2/checkout/orders'
            && $request['purchase_units'][0]['amount']['currency_code'] === 'USD'
            && $request['purchase_units'][0]['amount']['value'] === '0.60';
    });
});

it('creates a PayPal card order and stores pending checkout data in session', function () {
    config()->set('services.paypal.client_id', 'paypal-client');
    config()->set('services.paypal.client_secret', 'paypal-secret');
    config()->set('services.paypal.base_url', 'https://api-m.sandbox.paypal.com');

    ExchangeRate::factory()->create([
        'rate' => '0.00333333',
        'fetched_at' => now()->subHour(),
    ]);

    Http::fake([
        'https://api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response([
            'access_token' => 'paypal-access-token',
        ]),
        'https://api-m.sandbox.paypal.com/v2/checkout/orders' => Http::response([
            'id' => 'PAYPAL-CARD-ORDER-1',
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
        'currency' => 'LKR',
    ])->items()->create([
        'product_id' => $product->id,
        'quantity' => 1,
        'unit_price' => '180.00',
    ]);

    $payload = [
        'guest_name' => 'Heritage Patron',
        'guest_email' => 'patron@example.com',
        'currency' => 'LKR',
        'shipping_responsibility' => 'vendor',
        'payment_method' => 'paypal_card',
        'paypal_conversion_confirmed' => true,
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
        ->postJson(route('checkout.paypal.card.create'), $payload);

    $response
        ->assertOk()
        ->assertJsonPath('order_id', 'PAYPAL-CARD-ORDER-1');

    $response->assertSessionHas('checkout.paypal.pending.PAYPAL-CARD-ORDER-1.data.payment_method', 'paypal_card');
    $response->assertSessionHas('checkout.paypal.pending.PAYPAL-CARD-ORDER-1.quote.original_amount', '180.00');
    $response->assertSessionHas('checkout.paypal.pending.PAYPAL-CARD-ORDER-1.quote.converted_amount', '0.60');
    $response->assertSessionHas('checkout.paypal.pending.PAYPAL-CARD-ORDER-1.quote.converted_currency', 'USD');

    Http::assertSent(function (\Illuminate\Http\Client\Request $request): bool {
        return $request->url() === 'https://api-m.sandbox.paypal.com/v2/checkout/orders'
            && $request['purchase_units'][0]['amount']['currency_code'] === 'USD'
            && $request['purchase_units'][0]['amount']['value'] === '0.60';
    });
});

it('requires paypal conversion confirmation before creating a paypal order', function () {
    config()->set('services.paypal.client_id', 'paypal-client');
    config()->set('services.paypal.client_secret', 'paypal-secret');

    ExchangeRate::factory()->create([
        'fetched_at' => now()->subHour(),
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
        'currency' => 'LKR',
    ])->items()->create([
        'product_id' => $product->id,
        'quantity' => 1,
        'unit_price' => '180.00',
    ]);

    $response = $this
        ->withCookie('loomcraft_guest_token', 'guest-token')
        ->postJson(route('checkout.paypal.create'), [
            'guest_name' => 'Heritage Patron',
            'guest_email' => 'patron@example.com',
            'currency' => 'LKR',
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
        ]);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['paypal_conversion_confirmed']);

    Http::assertNothingSent();
});

it('blocks paypal checkout when the latest exchange rate is stale', function () {
    config()->set('services.paypal.client_id', 'paypal-client');
    config()->set('services.paypal.client_secret', 'paypal-secret');

    ExchangeRate::factory()->create([
        'fetched_at' => now()->subDays(2),
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
        'currency' => 'LKR',
    ])->items()->create([
        'product_id' => $product->id,
        'quantity' => 1,
        'unit_price' => '180.00',
    ]);

    $response = $this
        ->withCookie('loomcraft_guest_token', 'guest-token')
        ->postJson(route('checkout.paypal.create'), [
            'guest_name' => 'Heritage Patron',
            'guest_email' => 'patron@example.com',
            'currency' => 'LKR',
            'shipping_responsibility' => 'vendor',
            'payment_method' => 'paypal',
            'paypal_conversion_confirmed' => true,
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
        ]);

    $response
        ->assertStatus(422)
        ->assertJsonValidationErrors(['payment_method']);

    Http::assertNothingSent();
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
        'currency' => 'LKR',
    ]);

    $cart->items()->create([
        'product_id' => $product->id,
        'quantity' => 1,
        'unit_price' => '180.00',
    ]);

    $payload = [
        'guest_name' => 'Heritage Patron',
        'guest_email' => 'patron@example.com',
        'currency' => 'LKR',
        'shipping_responsibility' => 'vendor',
        'payment_method' => 'paypal',
        'paypal_conversion_confirmed' => true,
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
                    'quote' => [
                        'original_amount' => '180.00',
                        'original_currency' => 'LKR',
                        'converted_amount' => '0.60',
                        'converted_currency' => 'USD',
                        'exchange_rate' => '0.00333333',
                        'source' => 'open_er_api',
                        'fetched_at' => now()->subHour()->toIso8601String(),
                    ],
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
        'amount' => '0.60',
        'currency' => 'USD',
        'original_amount' => '180.00',
        'original_currency' => 'LKR',
        'exchange_rate' => '0.00333333',
        'exchange_rate_source' => 'open_er_api',
        'provider_reference' => 'PAYPAL-CAPTURE-1',
    ]);

    $this->assertDatabaseMissing('cart_items', [
        'cart_id' => $cart->id,
    ]);
});

it('captures a PayPal card order and creates the final order', function () {
    config()->set('services.paypal.client_id', 'paypal-client');
    config()->set('services.paypal.client_secret', 'paypal-secret');
    config()->set('services.paypal.base_url', 'https://api-m.sandbox.paypal.com');

    Http::fake([
        'https://api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response([
            'access_token' => 'paypal-access-token',
        ]),
        'https://api-m.sandbox.paypal.com/v2/checkout/orders/PAYPAL-CARD-ORDER-1/capture' => Http::response([
            'status' => 'COMPLETED',
            'purchase_units' => [
                [
                    'payments' => [
                        'captures' => [
                            [
                                'id' => 'PAYPAL-CARD-CAPTURE-1',
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
        'currency' => 'LKR',
    ]);

    $cart->items()->create([
        'product_id' => $product->id,
        'quantity' => 1,
        'unit_price' => '180.00',
    ]);

    $payload = [
        'guest_name' => 'Heritage Patron',
        'guest_email' => 'patron@example.com',
        'currency' => 'LKR',
        'shipping_responsibility' => 'vendor',
        'payment_method' => 'paypal_card',
        'paypal_conversion_confirmed' => true,
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
                'PAYPAL-CARD-ORDER-1' => [
                    'data' => $payload,
                    'quote' => [
                        'original_amount' => '180.00',
                        'original_currency' => 'LKR',
                        'converted_amount' => '0.60',
                        'converted_currency' => 'USD',
                        'exchange_rate' => '0.00333333',
                        'source' => 'open_er_api',
                        'fetched_at' => now()->subHour()->toIso8601String(),
                    ],
                    'guest_token' => 'guest-token',
                    'created_at' => now()->timestamp,
                ],
            ],
        ])
        ->postJson(route('checkout.paypal.card.capture'), [
            'order_id' => 'PAYPAL-CARD-ORDER-1',
        ]);

    $order = Order::query()->firstOrFail();

    $response
        ->assertOk()
        ->assertJsonPath('redirect_url', route('orders.confirmation', ['order' => $order->id]));

    $this->assertDatabaseHas('payments', [
        'order_id' => $order->id,
        'method' => 'paypal_card',
        'status' => 'paid',
        'amount' => '0.60',
        'currency' => 'USD',
        'original_amount' => '180.00',
        'original_currency' => 'LKR',
        'exchange_rate' => '0.00333333',
        'exchange_rate_source' => 'open_er_api',
        'provider_reference' => 'PAYPAL-CARD-CAPTURE-1',
    ]);

    $this->assertDatabaseMissing('cart_items', [
        'cart_id' => $cart->id,
    ]);
});
