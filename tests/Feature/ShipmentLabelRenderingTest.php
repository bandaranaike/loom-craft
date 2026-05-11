<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('renders an admin printable shipment label with live order data', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $order = createShipmentLabelOrder();
    $shipment = $order->shipments()->firstOrFail();

    $response = $this->actingAs($admin)
        ->get(route('admin.orders.shipments.label.show', ['order' => $order->id, 'shipment' => $shipment->id]));

    $response->assertOk()
        ->assertSee('LoomCraft', false)
        ->assertSee($order->order_number, false)
        ->assertSee($order->invoice->invoice_number, false)
        ->assertSee($shipment->shipment_number, false)
        ->assertSee('7734567890', false)
        ->assertSee('DHL eCommerce', false)
        ->assertSee('Guest Buyer', false)
        ->assertSee('Handwoven Cotton Area Rug', false)
        ->assertSee('PRD-LABEL-001', false);
});

it('renders the mobile api label for authorized sticker tokens', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $order = createShipmentLabelOrder();
    $shipment = $order->shipments()->firstOrFail();

    Sanctum::actingAs($admin, ['stickers:read']);

    $this->get("/api/v1/admin/orders/{$order->id}/shipments/{$shipment->id}/label")
        ->assertOk()
        ->assertSee($shipment->tracking_number, false)
        ->assertSee('Print', false);
});

it('rejects mobile api label rendering without sticker scope', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $order = createShipmentLabelOrder();
    $shipment = $order->shipments()->firstOrFail();

    Sanctum::actingAs($admin, ['orders:read']);

    $this->get("/api/v1/admin/orders/{$order->id}/shipments/{$shipment->id}/label")
        ->assertForbidden();
});

function createShipmentLabelOrder(): Order
{
    $vendorUser = User::factory()->create(['role' => 'vendor']);
    $vendor = Vendor::factory()->for($vendorUser)->create([
        'display_name' => 'Kandy Loom Studio',
        'status' => 'approved',
    ]);

    $product = Product::factory()->for($vendor)->create([
        'name' => 'Handwoven Cotton Area Rug',
        'product_code' => 'PRD-LABEL-001',
        'status' => 'active',
        'selling_price' => '180.00',
        'dimension_length' => '160.00',
        'dimension_width' => '230.00',
        'dimension_height' => '2.00',
        'dimension_unit' => 'cm',
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

    $order->addresses()->create([
        'type' => 'shipping',
        'full_name' => 'Guest Buyer',
        'line1' => '1 Loom Street',
        'line2' => 'Peradeniya',
        'city' => 'Kandy',
        'region' => 'Central',
        'postal_code' => '20000',
        'country_code' => 'LK',
        'phone' => '0770000000',
    ]);

    $order->shipments()->create([
        'vendor_id' => $vendor->id,
        'responsibility' => 'platform',
        'status' => 'ready_for_dispatch',
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
        'method' => 'bank_transfer',
        'status' => 'paid',
        'amount' => '180.00',
        'currency' => 'LKR',
        'original_amount' => '180.00',
        'original_currency' => 'LKR',
    ]);

    return $order->fresh(['invoice', 'items.product', 'items.vendor', 'addresses', 'shipments', 'payment']);
}
