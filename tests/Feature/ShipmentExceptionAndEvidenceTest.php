<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('adds shipment delivery evidence and exception fields', function () {
    expect(Schema::hasColumns('shipments', [
        'delivery_recipient_name',
        'delivery_proof_reference',
        'delivery_evidence_path',
        'delivery_evidence_original_name',
        'delivery_evidence_mime_type',
        'delivery_evidence_uploaded_at',
        'delivery_confirmed_by',
        'delivery_note',
        'delivery_exception_reason',
        'delivery_exception_note',
        'delivery_exception_at',
        'failed_delivery_attempts',
    ]))->toBeTrue();
});

it('requires a reason when marking delivery failed', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $order = createShipmentExceptionOrder('in_transit');
    $shipment = $order->shipments()->firstOrFail();

    $this->actingAs($admin)
        ->from(route('admin.orders.show', ['order' => $order->id]))
        ->patch(route('admin.orders.shipments.status.update', ['order' => $order->id, 'shipment' => $shipment->id]), [
            'shipment_status' => 'delivery_failed',
        ])
        ->assertRedirect(route('admin.orders.show', ['order' => $order->id]))
        ->assertSessionHasErrors('delivery_exception_reason');
});

it('records delivery exception details on failed delivery', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $order = createShipmentExceptionOrder('in_transit');
    $shipment = $order->shipments()->firstOrFail();

    $this->actingAs($admin)
        ->from(route('admin.orders.show', ['order' => $order->id]))
        ->patch(route('admin.orders.shipments.status.update', ['order' => $order->id, 'shipment' => $shipment->id]), [
            'shipment_status' => 'delivery_failed',
            'delivery_exception_reason' => 'customer_unreachable',
            'delivery_exception_note' => 'Courier called twice and no one answered.',
        ])
        ->assertRedirect(route('admin.orders.show', ['order' => $order->id]))
        ->assertSessionHas('status', 'Shipment status updated.');

    $shipment->refresh();

    expect($shipment->status)->toBe('delivery_failed')
        ->and($shipment->delivery_exception_reason)->toBe('customer_unreachable')
        ->and($shipment->delivery_exception_note)->toBe('Courier called twice and no one answered.')
        ->and($shipment->delivery_exception_at)->not->toBeNull()
        ->and($shipment->failed_delivery_attempts)->toBe(1);

    $this->assertDatabaseHas('fulfillment_status_histories', [
        'order_id' => $order->id,
        'shipment_id' => $shipment->id,
        'domain' => 'shipment',
        'from_status' => 'in_transit',
        'to_status' => 'delivery_failed',
        'reason' => 'delivery_exception',
        'actor_id' => $admin->id,
    ]);
});

it('records delivery evidence and uploaded proof', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => 'admin']);
    $order = createShipmentExceptionOrder('delivered');
    $shipment = $order->shipments()->firstOrFail();
    $file = UploadedFile::fake()->create('proof.pdf', 64, 'application/pdf');

    $this->actingAs($admin)
        ->from(route('admin.orders.show', ['order' => $order->id]))
        ->patch(route('admin.orders.shipments.delivery-evidence.update', ['order' => $order->id, 'shipment' => $shipment->id]), [
            'recipient_name' => 'Nimal Perera',
            'proof_reference' => 'POD-001',
            'evidence' => $file,
            'note' => 'Signed by customer.',
        ])
        ->assertRedirect(route('admin.orders.show', ['order' => $order->id]))
        ->assertSessionHas('status', 'Delivery evidence updated.');

    $shipment->refresh();

    expect($shipment->delivery_recipient_name)->toBe('Nimal Perera')
        ->and($shipment->delivery_proof_reference)->toBe('POD-001')
        ->and($shipment->delivery_confirmed_by)->toBe($admin->id)
        ->and($shipment->delivery_evidence_uploaded_at)->not->toBeNull()
        ->and($shipment->delivery_note)->toBe('Signed by customer.');

    Storage::disk('public')->assertExists($shipment->delivery_evidence_path);

    $this->assertDatabaseHas('fulfillment_status_histories', [
        'order_id' => $order->id,
        'shipment_id' => $shipment->id,
        'domain' => 'shipment',
        'reason' => 'delivery_evidence_recorded',
        'actor_id' => $admin->id,
    ]);
});

function createShipmentExceptionOrder(string $shipmentStatus): Order
{
    $vendorUser = User::factory()->create(['role' => 'vendor']);
    $vendor = Vendor::factory()->for($vendorUser)->create(['status' => 'approved']);
    $product = Product::factory()->for($vendor)->create(['status' => 'active', 'selling_price' => '180.00']);

    $order = Order::query()->create([
        'status' => $shipmentStatus === 'delivered' ? 'fulfilled' : 'confirmed',
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
        'status' => $shipmentStatus,
        'carrier' => 'Sri Lanka Post Courier',
        'service_level' => 'Standard',
        'tracking_number' => 'SLP-OUT-003',
        'package_count' => 1,
        'shipped_at' => now()->subDays(2),
        'delivered_at' => $shipmentStatus === 'delivered' ? now()->subDay() : null,
    ]);

    $order->payment()->create([
        'method' => 'cod',
        'status' => 'paid',
        'amount' => '180.00',
        'currency' => 'LKR',
        'original_amount' => '180.00',
        'original_currency' => 'LKR',
    ]);

    return $order->fresh(['items', 'payment', 'shipments']);
}
