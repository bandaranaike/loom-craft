<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('records immutable shipment and order history when a delivery fulfills an order', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $order = createFulfillmentWorkflowOrder(
        orderStatus: 'confirmed',
        shipmentStatus: 'in_transit',
    );
    $shipment = $order->shipments()->firstOrFail();

    $this->actingAs($admin)
        ->from(route('admin.orders.show', ['order' => $order->id]))
        ->patch(route('admin.orders.shipments.status.update', ['order' => $order->id, 'shipment' => $shipment->id]), [
            'shipment_status' => 'delivered',
        ])
        ->assertRedirect(route('admin.orders.show', ['order' => $order->id]))
        ->assertSessionHas('status', 'Shipment status updated.');

    $this->assertDatabaseHas('shipments', [
        'id' => $shipment->id,
        'status' => 'delivered',
    ]);

    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'status' => 'fulfilled',
    ]);

    $this->assertDatabaseHas('fulfillment_status_histories', [
        'order_id' => $order->id,
        'shipment_id' => $shipment->id,
        'domain' => 'shipment',
        'from_status' => 'in_transit',
        'to_status' => 'delivered',
        'actor_id' => $admin->id,
    ]);

    $this->assertDatabaseHas('fulfillment_status_histories', [
        'order_id' => $order->id,
        'shipment_id' => $shipment->id,
        'domain' => 'order',
        'from_status' => 'confirmed',
        'to_status' => 'fulfilled',
        'reason' => 'shipment_delivered',
        'actor_id' => $admin->id,
    ]);
});

it('prevents admins from cancelling an order after dispatch', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $order = createFulfillmentWorkflowOrder(
        orderStatus: 'confirmed',
        shipmentStatus: 'dispatched',
    );

    $this->actingAs($admin)
        ->from(route('admin.orders.show', ['order' => $order->id]))
        ->patch(route('admin.orders.status.update', ['order' => $order->id]), [
            'order_status' => 'cancelled',
        ])
        ->assertRedirect(route('admin.orders.show', ['order' => $order->id]))
        ->assertSessionHasErrors('order_status');

    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'status' => 'confirmed',
    ]);
});

it('records offline payment history when admins verify payment state changes', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $order = createFulfillmentWorkflowOrder(
        orderStatus: 'pending',
        shipmentStatus: 'pending',
        paymentMethod: 'bank_transfer',
        paymentStatus: 'pending',
    );

    $this->actingAs($admin)
        ->from(route('admin.orders.show', ['order' => $order->id]))
        ->patch(route('admin.orders.offline.update', ['order' => $order->id]), [
            'payment_status' => 'paid',
        ])
        ->assertRedirect(route('admin.orders.show', ['order' => $order->id]))
        ->assertSessionHas('status', 'Offline payment status updated.');

    $payment = $order->payment()->firstOrFail();

    $this->assertDatabaseHas('fulfillment_status_histories', [
        'order_id' => $order->id,
        'payment_id' => $payment->id,
        'domain' => 'payment',
        'from_status' => 'pending',
        'to_status' => 'paid',
        'actor_id' => $admin->id,
    ]);
});

function createFulfillmentWorkflowOrder(
    string $orderStatus,
    string $shipmentStatus,
    string $paymentMethod = 'bank_transfer',
    string $paymentStatus = 'pending',
): Order {
    $vendorUser = User::factory()->create(['role' => 'vendor']);
    $vendor = Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
    ]);

    $product = Product::factory()->for($vendor)->create([
        'status' => 'active',
        'selling_price' => '180.00',
    ]);

    $order = Order::query()->create([
        'status' => $orderStatus,
        'currency' => 'LKR',
        'subtotal' => '180.00',
        'commission_total' => '180.00',
        'total' => '180.00',
        'shipping_responsibility' => 'platform',
        'placed_at' => now(),
        'guest_name' => 'Guest Buyer',
        'guest_email' => 'guest@example.com',
    ]);

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

    $order->shipments()->create([
        'vendor_id' => $vendor->id,
        'responsibility' => 'platform',
        'status' => $shipmentStatus,
        'carrier' => 'DHL eCommerce',
        'service_level' => 'Standard',
        'tracking_number' => '7734567890',
        'package_count' => 1,
        'parcel_weight' => '3.20',
        'weight_unit' => 'kg',
        'parcel_length' => '40.00',
        'parcel_width' => '28.00',
        'parcel_height' => '18.00',
        'parcel_dimension_unit' => 'cm',
    ]);

    $order->payment()->create([
        'method' => $paymentMethod,
        'status' => $paymentStatus,
        'amount' => '180.00',
        'currency' => 'LKR',
        'original_amount' => '180.00',
        'original_currency' => 'LKR',
    ]);

    return $order->fresh(['payment', 'shipments']);
}
