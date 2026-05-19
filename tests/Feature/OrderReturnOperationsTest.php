<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingCarrier;
use App\Models\ShippingService;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('adds the return schema needed for reverse logistics', function () {
    expect(Schema::hasTable('order_returns'))->toBeTrue()
        ->and(Schema::hasColumns('order_returns', [
            'return_number',
            'order_id',
            'shipment_id',
            'requested_by',
            'reviewed_by',
            'received_by',
            'status',
            'reason',
            'shipping_carrier_id',
            'shipping_service_id',
            'tracking_number',
            'admin_received_at',
            'inspected_at',
            'resolved_at',
            'closed_at',
        ]))->toBeTrue()
        ->and(Schema::hasTable('order_return_items'))->toBeTrue()
        ->and(Schema::hasColumns('order_return_items', [
            'order_return_id',
            'order_item_id',
            'quantity',
            'condition',
            'resolution',
            'note',
        ]))->toBeTrue()
        ->and(Schema::hasColumn('fulfillment_status_histories', 'order_return_id'))->toBeTrue();
});

it('allows admins to record a customer return request for delivered order items', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $order = createReturnableOrder();
    $shipment = $order->shipments()->firstOrFail();
    $orderItem = $order->items()->firstOrFail();

    $this->actingAs($admin)
        ->from(route('admin.orders.show', ['order' => $order->id]))
        ->post(route('admin.orders.returns.store', ['order' => $order->id]), [
            'shipment_id' => $shipment->id,
            'reason' => 'damaged_item',
            'customer_note' => 'The parcel arrived damaged.',
            'admin_note' => 'Photo evidence received by support.',
            'items' => [
                [
                    'order_item_id' => $orderItem->id,
                    'quantity' => 1,
                    'condition' => 'damaged',
                    'resolution' => 'refund',
                    'note' => 'Corner torn.',
                ],
            ],
        ])
        ->assertRedirect(route('admin.orders.show', ['order' => $order->id]))
        ->assertSessionHas('status', 'Return request recorded.');

    $orderReturn = $order->returns()->firstOrFail();

    expect($orderReturn->return_number)->toStartWith('RET-')
        ->and($orderReturn->status)->toBe('requested')
        ->and($orderReturn->requested_at)->not->toBeNull();

    $this->assertDatabaseHas('order_return_items', [
        'order_return_id' => $orderReturn->id,
        'order_item_id' => $orderItem->id,
        'quantity' => 1,
        'condition' => 'damaged',
        'resolution' => 'refund',
    ]);

    $this->assertDatabaseHas('fulfillment_status_histories', [
        'order_id' => $order->id,
        'order_return_id' => $orderReturn->id,
        'domain' => 'return',
        'from_status' => null,
        'to_status' => 'requested',
        'reason' => 'return_requested',
        'actor_id' => $admin->id,
    ]);
});

it('tracks the phase one return parcel workflow back to admin', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $order = createReturnableOrder();
    $carrier = ShippingCarrier::factory()->create(['name' => 'Sri Lanka Post Courier']);
    $service = ShippingService::factory()->for($carrier, 'carrier')->create(['name' => 'Standard']);
    $orderReturn = $order->returns()->create([
        'shipment_id' => $order->shipments()->firstOrFail()->id,
        'requested_by' => $admin->id,
        'status' => 'requested',
        'reason' => 'wrong_item',
        'requested_at' => now(),
    ]);
    $orderReturn->items()->create([
        'order_item_id' => $order->items()->firstOrFail()->id,
        'quantity' => 1,
    ]);

    $this->actingAs($admin)
        ->from(route('admin.orders.show', ['order' => $order->id]))
        ->patch(route('admin.orders.returns.status.update', ['order' => $order->id, 'orderReturn' => $orderReturn->id]), [
            'return_status' => 'approved',
            'note' => 'Approved for return to admin.',
        ])
        ->assertRedirect(route('admin.orders.show', ['order' => $order->id]))
        ->assertSessionHas('status', 'Return status updated.');

    $this->actingAs($admin)
        ->patch(route('admin.orders.returns.tracking.update', ['order' => $order->id, 'orderReturn' => $orderReturn->id]), [
            'shipping_carrier_id' => $carrier->id,
            'shipping_service_id' => $service->id,
            'tracking_number' => 'SLP-RET-001',
            'package_count' => 1,
            'parcel_weight' => '2.50',
            'weight_unit' => 'kg',
            'parcel_length' => '30.00',
            'parcel_width' => '20.00',
            'parcel_height' => '10.00',
            'parcel_dimension_unit' => 'cm',
        ])
        ->assertSessionHas('status', 'Return tracking updated.');

    foreach (['in_transit', 'received_by_admin', 'inspected', 'vendor_review', 'resolved', 'closed'] as $status) {
        $this->actingAs($admin)
            ->from(route('admin.orders.show', ['order' => $order->id]))
            ->patch(route('admin.orders.returns.status.update', ['order' => $order->id, 'orderReturn' => $orderReturn->id]), [
                'return_status' => $status,
                'resolution' => $status === 'resolved' ? 'refund' : null,
                'note' => "Moved to {$status}.",
            ])
            ->assertRedirect(route('admin.orders.show', ['order' => $order->id]))
            ->assertSessionHas('status', 'Return status updated.');
    }

    $orderReturn->refresh();

    expect($orderReturn->status)->toBe('closed')
        ->and($orderReturn->shipping_carrier_id)->toBe($carrier->id)
        ->and($orderReturn->shipping_service_id)->toBe($service->id)
        ->and($orderReturn->carrier)->toBe('Sri Lanka Post Courier')
        ->and($orderReturn->tracking_number)->toBe('SLP-RET-001')
        ->and($orderReturn->parcel_weight)->toBe('2.50')
        ->and($orderReturn->parcel_dimension_unit)->toBe('cm')
        ->and($orderReturn->approved_at)->not->toBeNull()
        ->and($orderReturn->in_transit_at)->not->toBeNull()
        ->and($orderReturn->admin_received_at)->not->toBeNull()
        ->and($orderReturn->inspected_at)->not->toBeNull()
        ->and($orderReturn->vendor_review_started_at)->not->toBeNull()
        ->and($orderReturn->resolved_at)->not->toBeNull()
        ->and($orderReturn->closed_at)->not->toBeNull();

    foreach ([
        ['requested', 'approved'],
        ['approved', 'in_transit'],
        ['in_transit', 'received_by_admin'],
        ['received_by_admin', 'inspected'],
        ['inspected', 'vendor_review'],
        ['vendor_review', 'resolved'],
        ['resolved', 'closed'],
    ] as [$fromStatus, $toStatus]) {
        $this->assertDatabaseHas('fulfillment_status_histories', [
            'order_id' => $order->id,
            'order_return_id' => $orderReturn->id,
            'domain' => 'return',
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'actor_id' => $admin->id,
        ]);
    }
});

it('prevents skipped return workflow transitions', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $order = createReturnableOrder();
    $orderReturn = $order->returns()->create([
        'status' => 'requested',
        'reason' => 'missing_item',
        'requested_at' => now(),
    ]);

    $this->actingAs($admin)
        ->from(route('admin.orders.show', ['order' => $order->id]))
        ->patch(route('admin.orders.returns.status.update', ['order' => $order->id, 'orderReturn' => $orderReturn->id]), [
            'return_status' => 'received_by_admin',
        ])
        ->assertRedirect(route('admin.orders.show', ['order' => $order->id]))
        ->assertSessionHasErrors('return_status');

    $this->assertDatabaseHas('order_returns', [
        'id' => $orderReturn->id,
        'status' => 'requested',
    ]);
});

function createReturnableOrder(): Order
{
    $vendorUser = User::factory()->create(['role' => 'vendor']);
    $vendor = Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
    ]);

    $product = Product::factory()->for($vendor)->create([
        'status' => 'active',
        'selling_price' => '180.00',
    ]);

    $order = Order::query()->create([
        'status' => 'fulfilled',
        'currency' => 'LKR',
        'subtotal' => '360.00',
        'commission_total' => '360.00',
        'total' => '360.00',
        'shipping_responsibility' => 'platform',
        'placed_at' => now(),
        'guest_name' => 'Guest Buyer',
        'guest_email' => 'guest@example.com',
    ]);

    $order->items()->create([
        'product_id' => $product->id,
        'vendor_id' => $vendor->id,
        'quantity' => 2,
        'unit_price' => '180.00',
        'commission_rate' => '100.00',
        'commission_amount' => '360.00',
        'line_total' => '360.00',
    ]);

    $order->shipments()->create([
        'vendor_id' => $vendor->id,
        'responsibility' => 'platform',
        'status' => 'delivered',
        'carrier' => 'Sri Lanka Post Courier',
        'service_level' => 'Standard',
        'tracking_number' => 'SLP-OUT-001',
        'package_count' => 1,
        'parcel_weight' => '3.20',
        'weight_unit' => 'kg',
        'parcel_length' => '40.00',
        'parcel_width' => '28.00',
        'parcel_height' => '18.00',
        'parcel_dimension_unit' => 'cm',
        'shipped_at' => now()->subDays(3),
        'delivered_at' => now()->subDay(),
    ]);

    $order->payment()->create([
        'method' => 'cod',
        'status' => 'paid',
        'amount' => '360.00',
        'currency' => 'LKR',
        'original_amount' => '360.00',
        'original_currency' => 'LKR',
    ]);

    return $order->fresh(['items', 'payment', 'shipments']);
}
