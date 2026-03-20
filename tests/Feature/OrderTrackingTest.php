<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

it('assigns a durable public identifier when an order is created', function () {
    $order = createTrackedOrder();

    expect($order->public_id)
        ->not->toBeNull()
        ->toStartWith('ORD-')
        ->and(strlen($order->public_id))->toBeGreaterThan(20);
});

it('resolves the customer order page by public identifier', function () {
    $order = createTrackedOrder();

    $this->withSession(['guest_order_id' => $order->id])
        ->get(route('orders.show', ['order' => $order->public_id]))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('orders/show')
            ->where('order.public_id', $order->public_id)
            ->where('order.id', $order->id)
        );
});

it('does not require the numeric internal id in the public customer order url', function () {
    $order = createTrackedOrder();

    expect($order->public_id)->not->toBe((string) $order->id);

    $this->get('/orders/'.$order->id)->assertNotFound();
});

it('shares order and payment currencies separately on the order page', function () {
    $order = createTrackedOrder([
        'currency' => 'LKR',
        'subtotal' => '180.00',
        'commission_total' => '180.00',
        'total' => '180.00',
    ], [
        'status' => 'paid',
        'amount' => '0.60',
        'currency' => 'USD',
        'original_amount' => '180.00',
        'original_currency' => 'LKR',
    ]);

    $this->withSession(['guest_order_id' => $order->id])
        ->get(route('orders.show', ['order' => $order->public_id]))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('orders/show')
            ->where('order.currency', 'LKR')
            ->where('order.payment_currency', 'USD')
            ->where('order.payment_original_currency', 'LKR')
            ->where('order.payment_amount', '0.60')
        );
});

it('exposes delivered progress states on the order page', function () {
    $order = createTrackedOrder([
        'status' => 'delivered',
    ], [
        'status' => 'paid',
    ]);

    $this->withSession(['guest_order_id' => $order->id])
        ->get(route('orders.show', ['order' => $order->public_id]))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('orders/show')
            ->where('order.progress.is_cancelled', false)
            ->where('order.progress.steps.0.state', 'complete')
            ->where('order.progress.steps.1.state', 'complete')
            ->where('order.progress.steps.2.state', 'complete')
            ->where('order.progress.steps.3.state', 'complete')
            ->where('order.progress.steps.4.state', 'current')
        );
});

it('marks payment as complete once payment succeeds even before confirmation', function () {
    $order = createTrackedOrder([
        'status' => 'pending',
    ], [
        'status' => 'paid',
    ]);

    $this->withSession(['guest_order_id' => $order->id])
        ->get(route('orders.show', ['order' => $order->public_id]))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('orders/show')
            ->where('order.progress.steps.0.state', 'complete')
            ->where('order.progress.steps.1.state', 'complete')
            ->where('order.progress.steps.2.state', 'upcoming')
            ->where('order.progress.steps.3.state', 'upcoming')
            ->where('order.progress.steps.4.state', 'upcoming')
        );
});

it('exposes a distinct cancelled summary card payload', function () {
    $order = createTrackedOrder([
        'status' => 'cancelled',
    ], [
        'status' => 'pending',
    ]);

    $this->withSession(['guest_order_id' => $order->id])
        ->get(route('orders.show', ['order' => $order->public_id]))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('orders/show')
            ->where('order.progress.is_cancelled', true)
            ->where('order.progress.summary.title', 'Order cancelled')
        );
});

function createTrackedOrder(array $orderOverrides = [], array $paymentOverrides = []): Order
{
    $vendorUser = User::factory()->create(['role' => 'vendor']);
    $vendor = Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
    ]);

    $product = Product::factory()->for($vendor)->create([
        'status' => 'active',
        'selling_price' => '180.00',
    ]);

    $order = Order::query()->create(array_merge([
        'status' => 'pending',
        'currency' => 'LKR',
        'subtotal' => '180.00',
        'commission_total' => '180.00',
        'total' => '180.00',
        'shipping_responsibility' => 'platform',
        'placed_at' => now(),
        'guest_name' => 'Guest Buyer',
        'guest_email' => 'guest@example.com',
    ], $orderOverrides));

    $order->items()->create([
        'product_id' => $product->id,
        'vendor_id' => $vendor->id,
        'quantity' => 1,
        'unit_price' => '180.00',
        'commission_rate' => '100.00',
        'commission_amount' => '180.00',
        'line_total' => '180.00',
    ]);

    $order->addresses()->createMany([
        [
            'type' => 'shipping',
            'full_name' => 'Guest Buyer',
            'line1' => '1 Loom Street',
            'line2' => null,
            'city' => 'Kandy',
            'region' => 'Central',
            'postal_code' => '20000',
            'country_code' => 'LK',
            'phone' => '0770000000',
        ],
        [
            'type' => 'billing',
            'full_name' => 'Guest Buyer',
            'line1' => '1 Loom Street',
            'line2' => null,
            'city' => 'Kandy',
            'region' => 'Central',
            'postal_code' => '20000',
            'country_code' => 'LK',
            'phone' => '0770000000',
        ],
    ]);

    $order->payment()->create(array_merge([
        'method' => 'bank_transfer',
        'status' => 'pending',
        'amount' => '180.00',
        'currency' => 'LKR',
        'original_amount' => '180.00',
        'original_currency' => 'LKR',
    ], $paymentOverrides));

    return $order->fresh(['payment']);
}
