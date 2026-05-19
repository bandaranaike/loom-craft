<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('assigns an operational order number and invoice when an order is created', function () {
    $order = createFulfillmentFoundationOrder();

    expect($order->order_number)
        ->toMatch('/^ORD-\d{6}-\d{6}$/')
        ->and($order->invoice)->not->toBeNull()
        ->and($order->invoice->invoice_number)->toMatch('/^INV-\d{6}-\d{6}$/')
        ->and($order->invoice->currency)->toBe('LKR')
        ->and((float) $order->invoice->total)->toBe(180.0);
});

it('assigns a shipment number and preserves parcel metrics on shipments', function () {
    $order = createFulfillmentFoundationOrder();

    $shipment = $order->shipments()->create([
        'vendor_id' => $order->items()->first()->vendor_id,
        'responsibility' => 'platform',
        'status' => 'pending',
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

    expect($shipment->shipment_number)
        ->toMatch('/^SHP-\d{6}-\d{6}$/')
        ->and($shipment->service_level)->toBe('Standard')
        ->and($shipment->package_count)->toBe(1)
        ->and($shipment->parcel_weight)->toBe('3.20')
        ->and($shipment->weight_unit)->toBe('kg')
        ->and($shipment->parcel_dimension_unit)->toBe('cm');
});

it('represents shipment item allocation for multi-vendor orders', function () {
    $order = createFulfillmentFoundationOrder();
    $otherVendor = Vendor::factory()->create(['status' => 'approved']);
    $otherProduct = Product::factory()->for($otherVendor)->create([
        'status' => 'active',
        'selling_price' => '220.00',
    ]);

    $otherItem = $order->items()->create([
        'product_id' => $otherProduct->id,
        'vendor_id' => $otherVendor->id,
        'quantity' => 2,
        'unit_price' => '220.00',
        'commission_rate' => '100.00',
        'commission_amount' => '440.00',
        'line_total' => '440.00',
    ]);

    $shipment = $order->shipments()->create([
        'vendor_id' => null,
        'responsibility' => 'platform',
        'status' => 'pending',
        'package_count' => 1,
    ]);

    foreach ($order->items as $item) {
        $shipment->items()->create([
            'order_item_id' => $item->id,
            'quantity' => $item->quantity,
        ]);
    }

    expect($shipment->vendor_id)->toBeNull()
        ->and($shipment->items()->count())->toBe(2);

    $this->assertDatabaseHas('shipment_items', [
        'shipment_id' => $shipment->id,
        'order_item_id' => $otherItem->id,
        'quantity' => 2,
    ]);
});

function createFulfillmentFoundationOrder(): Order
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
        'status' => 'pending',
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

    return $order->fresh(['invoice']);
}
