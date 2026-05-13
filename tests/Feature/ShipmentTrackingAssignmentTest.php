<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingCarrier;
use App\Models\ShippingService;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows admins to assign courier tracking to a shipment', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $order = createShipmentTrackingOrder();
    $shipment = $order->shipments()->firstOrFail();
    $carrier = ShippingCarrier::factory()->create(['name' => 'DHL eCommerce']);
    $service = ShippingService::factory()->for($carrier, 'carrier')->create(['name' => 'Standard']);

    $this->actingAs($admin)
        ->from(route('admin.orders.show', ['order' => $order->id]))
        ->patch(route('admin.orders.shipments.tracking.update', ['order' => $order->id, 'shipment' => $shipment->id]), [
            'shipping_carrier_id' => $carrier->id,
            'shipping_service_id' => $service->id,
            'tracking_number' => '7734567890',
        ])
        ->assertRedirect(route('admin.orders.show', ['order' => $order->id]))
        ->assertSessionHas('status', 'Shipment tracking updated.');

    $this->assertDatabaseHas('shipments', [
        'id' => $shipment->id,
        'shipping_carrier_id' => $carrier->id,
        'shipping_service_id' => $service->id,
        'carrier' => 'DHL eCommerce',
        'service_level' => 'Standard',
        'tracking_number' => '7734567890',
    ]);

    $this->assertDatabaseHas('fulfillment_status_histories', [
        'order_id' => $order->id,
        'shipment_id' => $shipment->id,
        'domain' => 'shipment',
        'from_status' => 'ready_for_dispatch',
        'to_status' => 'ready_for_dispatch',
        'reason' => 'tracking_updated',
        'actor_id' => $admin->id,
    ]);
});

it('prevents shipment dispatch until courier tracking is assigned', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $order = createShipmentTrackingOrder();
    $shipment = $order->shipments()->firstOrFail();

    $this->actingAs($admin)
        ->from(route('admin.orders.show', ['order' => $order->id]))
        ->patch(route('admin.orders.shipments.status.update', ['order' => $order->id, 'shipment' => $shipment->id]), [
            'shipment_status' => 'dispatched',
        ])
        ->assertRedirect(route('admin.orders.show', ['order' => $order->id]))
        ->assertSessionHasErrors([
            'shipment_status' => 'Assign a courier carrier and tracking number before dispatching this shipment.',
        ]);

    $this->assertDatabaseHas('shipments', [
        'id' => $shipment->id,
        'status' => 'ready_for_dispatch',
        'tracking_number' => null,
    ]);
});

it('allows shipment dispatch after courier tracking is assigned', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $order = createShipmentTrackingOrder(
        carrier: 'DHL eCommerce',
        trackingNumber: '7734567890',
    );
    $shipment = $order->shipments()->firstOrFail();

    $this->actingAs($admin)
        ->from(route('admin.orders.show', ['order' => $order->id]))
        ->patch(route('admin.orders.shipments.status.update', ['order' => $order->id, 'shipment' => $shipment->id]), [
            'shipment_status' => 'dispatched',
        ])
        ->assertRedirect(route('admin.orders.show', ['order' => $order->id]))
        ->assertSessionHas('status', 'Shipment status updated.');

    $this->assertDatabaseHas('shipments', [
        'id' => $shipment->id,
        'status' => 'dispatched',
    ]);

    expect($shipment->fresh()->shipped_at)->not->toBeNull();
});

function createShipmentTrackingOrder(
    ?string $carrier = null,
    ?string $trackingNumber = null,
): Order {
    $vendorUser = User::factory()->create(['role' => 'vendor']);
    $shippingCarrier = $carrier === null ? null : ShippingCarrier::factory()->create(['name' => $carrier]);
    $shippingService = $shippingCarrier === null ? null : ShippingService::factory()->for($shippingCarrier, 'carrier')->create(['name' => 'Standard']);
    $vendor = Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
    ]);

    $product = Product::factory()->for($vendor)->create([
        'status' => 'active',
        'selling_price' => '180.00',
    ]);

    $order = Order::query()->create([
        'status' => 'confirmed',
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

    $order->shipments()->create([
        'vendor_id' => $vendor->id,
        'responsibility' => 'platform',
        'status' => 'ready_for_dispatch',
        'shipping_carrier_id' => $shippingCarrier?->id,
        'shipping_service_id' => $shippingService?->id,
        'carrier' => $carrier,
        'service_level' => 'Standard',
        'tracking_number' => $trackingNumber,
        'package_count' => 1,
        'parcel_weight' => '3.20',
        'weight_unit' => 'kg',
        'parcel_length' => '40.00',
        'parcel_width' => '28.00',
        'parcel_height' => '18.00',
        'parcel_dimension_unit' => 'cm',
    ]);

    $order->payment()->create([
        'method' => 'bank_transfer',
        'status' => 'paid',
        'amount' => '180.00',
        'currency' => 'LKR',
        'original_amount' => '180.00',
        'original_currency' => 'LKR',
    ]);

    return $order->fresh(['payment', 'shipments']);
}
