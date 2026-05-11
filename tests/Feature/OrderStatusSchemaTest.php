<?php

use App\Models\Order;
use Illuminate\Database\QueryException;

it('allows every supported order status to be persisted', function () {
    foreach (supportedOrderStatuses() as $status) {
        $order = Order::query()->create(orderAttributes(['status' => $status]));

        expect($order->status)->toBe($status);
    }
});

it('rejects unsupported order statuses at the database layer', function () {
    expect(fn () => Order::query()->create(orderAttributes(['status' => 'processing'])))
        ->toThrow(QueryException::class);
});

/**
 * @return list<string>
 */
function supportedOrderStatuses(): array
{
    return ['pending', 'paid', 'confirmed', 'fulfilled', 'closed', 'cancelled'];
}

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function orderAttributes(array $overrides = []): array
{
    return array_merge([
        'guest_name' => 'Schema Test Customer',
        'guest_email' => 'schema-test@example.com',
        'status' => 'pending',
        'currency' => 'LKR',
        'subtotal' => '100.00',
        'commission_total' => '10.00',
        'total' => '110.00',
        'shipping_responsibility' => 'vendor',
        'placed_at' => now(),
    ], $overrides);
}
