<?php

use App\Models\Complaint;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('adds the complaint schema needed for operational resolution', function () {
    expect(Schema::hasColumns('complaints', [
        'complaint_number',
        'order_id',
        'shipment_id',
        'order_return_id',
        'payment_id',
        'category',
        'severity',
        'resolution_type',
        'resolution_note',
        'courier_claim_reference',
        'assigned_to',
        'opened_at',
        'first_response_due_at',
        'sla_due_at',
        'first_responded_at',
        'resolved_at',
        'closed_at',
    ]))->toBeTrue()
        ->and(Schema::hasColumn('fulfillment_status_histories', 'complaint_id'))->toBeTrue();
});

it('allows admins to record a complaint linked to fulfillment records', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $order = createComplaintOrder();
    $shipment = $order->shipments()->firstOrFail();
    $payment = $order->payment()->firstOrFail();
    $orderReturn = $order->returns()->create([
        'shipment_id' => $shipment->id,
        'requested_by' => $admin->id,
        'status' => 'requested',
        'reason' => 'damaged_item',
        'requested_at' => now(),
    ]);

    $this->actingAs($admin)
        ->from(route('admin.orders.show', ['order' => $order->id]))
        ->post(route('admin.orders.complaints.store', ['order' => $order->id]), [
            'shipment_id' => $shipment->id,
            'order_return_id' => $orderReturn->id,
            'payment_id' => $payment->id,
            'category' => 'damaged_item',
            'severity' => 'high',
            'subject' => 'Customer reported damaged item',
            'message' => 'The package arrived with a torn corner.',
            'resolution_type' => 'refund',
            'courier_claim_reference' => 'SLP-CLAIM-001',
        ])
        ->assertRedirect(route('admin.orders.show', ['order' => $order->id]))
        ->assertSessionHas('status', 'Complaint recorded.');

    $complaint = $order->complaints()->firstOrFail();

    expect($complaint->complaint_number)->toStartWith('CMP-')
        ->and($complaint->status)->toBe('open')
        ->and($complaint->category)->toBe('damaged_item')
        ->and($complaint->severity)->toBe('high')
        ->and($complaint->shipment_id)->toBe($shipment->id)
        ->and($complaint->order_return_id)->toBe($orderReturn->id)
        ->and($complaint->payment_id)->toBe($payment->id)
        ->and($complaint->opened_at)->not->toBeNull()
        ->and($complaint->first_response_due_at)->not->toBeNull()
        ->and($complaint->sla_due_at)->not->toBeNull();

    $this->assertDatabaseHas('fulfillment_status_histories', [
        'order_id' => $order->id,
        'complaint_id' => $complaint->id,
        'domain' => 'complaint',
        'from_status' => null,
        'to_status' => 'open',
        'reason' => 'complaint_opened',
        'actor_id' => $admin->id,
    ]);
});

it('tracks complaint resolution status changes with history', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $order = createComplaintOrder();
    $complaint = Complaint::factory()->for($order)->create([
        'status' => 'open',
        'category' => 'late_delivery',
        'severity' => 'normal',
    ]);

    foreach (['in_review', 'waiting_for_courier', 'in_review'] as $status) {
        $this->actingAs($admin)
            ->from(route('admin.orders.show', ['order' => $order->id]))
            ->patch(route('admin.complaints.status.update', ['complaint' => $complaint->id]), [
                'complaint_status' => $status,
                'note' => "Moved to {$status}.",
            ])
            ->assertRedirect(route('admin.orders.show', ['order' => $order->id]))
            ->assertSessionHas('status', 'Complaint status updated.');
    }

    $this->actingAs($admin)
        ->from(route('admin.orders.show', ['order' => $order->id]))
        ->patch(route('admin.complaints.status.update', ['complaint' => $complaint->id]), [
            'complaint_status' => 'resolved',
            'resolution_type' => 'courier_claim',
            'resolution_note' => 'Courier claim submitted and customer informed.',
            'courier_claim_reference' => 'SLP-CLAIM-002',
        ])
        ->assertRedirect(route('admin.orders.show', ['order' => $order->id]))
        ->assertSessionHas('status', 'Complaint status updated.');

    $this->actingAs($admin)
        ->from(route('admin.orders.show', ['order' => $order->id]))
        ->patch(route('admin.complaints.status.update', ['complaint' => $complaint->id]), [
            'complaint_status' => 'closed',
        ])
        ->assertRedirect(route('admin.orders.show', ['order' => $order->id]))
        ->assertSessionHas('status', 'Complaint status updated.');

    $complaint->refresh();

    expect($complaint->status)->toBe('closed')
        ->and($complaint->first_responded_at)->not->toBeNull()
        ->and($complaint->resolved_at)->not->toBeNull()
        ->and($complaint->closed_at)->not->toBeNull()
        ->and($complaint->resolution_type)->toBe('courier_claim')
        ->and($complaint->courier_claim_reference)->toBe('SLP-CLAIM-002');

    foreach ([
        ['open', 'in_review'],
        ['in_review', 'waiting_for_courier'],
        ['waiting_for_courier', 'in_review'],
        ['in_review', 'resolved'],
        ['resolved', 'closed'],
    ] as [$fromStatus, $toStatus]) {
        $this->assertDatabaseHas('fulfillment_status_histories', [
            'order_id' => $order->id,
            'complaint_id' => $complaint->id,
            'domain' => 'complaint',
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'actor_id' => $admin->id,
        ]);
    }
});

it('prevents reopening closed complaints through skipped transitions', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $order = createComplaintOrder();
    $complaint = Complaint::factory()->for($order)->create([
        'status' => 'closed',
        'closed_at' => now(),
    ]);

    $this->actingAs($admin)
        ->from(route('admin.orders.show', ['order' => $order->id]))
        ->patch(route('admin.complaints.status.update', ['complaint' => $complaint->id]), [
            'complaint_status' => 'in_review',
        ])
        ->assertRedirect(route('admin.orders.show', ['order' => $order->id]))
        ->assertSessionHasErrors('complaint_status');

    $this->assertDatabaseHas('complaints', [
        'id' => $complaint->id,
        'status' => 'closed',
    ]);
});

function createComplaintOrder(): Order
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
        'status' => 'delivered',
        'carrier' => 'Sri Lanka Post Courier',
        'service_level' => 'Standard',
        'tracking_number' => 'SLP-OUT-002',
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
        'amount' => '180.00',
        'currency' => 'LKR',
        'original_amount' => '180.00',
        'original_currency' => 'LKR',
    ]);

    return $order->fresh(['items', 'payment', 'shipments']);
}
