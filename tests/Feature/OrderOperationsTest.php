<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

it('prevents customers from viewing another customers order page', function () {
    $owner = User::factory()->create(['role' => 'customer']);
    $otherCustomer = User::factory()->create(['role' => 'customer']);
    $order = createManagedOrder(customer: $owner);

    $this->actingAs($otherCustomer)
        ->get(route('orders.show', ['order' => $order->public_id]))
        ->assertForbidden();
});

it('allows admins to update order statuses through the business-state workflow', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $order = createManagedOrder();

    $this->actingAs($admin)
        ->from(route('admin.orders.show', ['order' => $order->id]))
        ->patch(route('admin.orders.status.update', ['order' => $order->id]), [
            'order_status' => 'confirmed',
        ])
        ->assertRedirect(route('admin.orders.show', ['order' => $order->id]))
        ->assertSessionHas('status', 'Order status updated.');

    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'status' => 'confirmed',
    ]);
});

it('redirects admins from the customer orders route to the admin orders route', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('orders.index'))
        ->assertRedirect(route('admin.orders.index'));
});

it('lists all orders for admins and supports filtering by status', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $pendingOrder = createManagedOrder();
    $fulfilledOrder = createManagedOrder();

    $pendingOrder->update(['status' => 'pending']);
    $fulfilledOrder->update(['status' => 'fulfilled']);

    $this->actingAs($admin)
        ->get(route('admin.orders.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/orders/index')
            ->has('orders', 2)
            ->where('selected_status', null)
            ->where('status_options', ['pending', 'paid', 'confirmed', 'fulfilled', 'closed', 'cancelled'])
        );

    $this->actingAs($admin)
        ->get(route('admin.orders.index', ['status' => 'fulfilled']))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/orders/index')
            ->has('orders', 1)
            ->where('orders.0.id', $fulfilledOrder->id)
            ->where('orders.0.order_number', $fulfilledOrder->order_number)
            ->where('orders.0.status', 'fulfilled')
            ->where('selected_status', 'fulfilled')
        );
});

it('soft deletes orders for admins', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create(['role' => 'customer']);
    $order = createManagedOrder(customer: $customer);

    $this->actingAs($admin)
        ->delete(route('admin.orders.destroy', ['order' => $order->id]))
        ->assertRedirect(route('admin.orders.index'))
        ->assertSessionHas('status', 'Order deleted.');

    $this->assertSoftDeleted('orders', [
        'id' => $order->id,
    ]);

    $this->actingAs($customer)
        ->get(route('orders.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('orders/index')
            ->has('orders', 0)
        );
});

it('lists only vendor related orders', function () {
    [$vendorUser, $vendor] = createApprovedVendor();
    [, $otherVendor] = createApprovedVendor();
    $matchingOrder = createManagedOrder(vendor: $vendor);
    createManagedOrder(vendor: $otherVendor);

    $this->actingAs($vendorUser)
        ->get(route('vendor.orders.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('vendor/orders/index')
            ->has('orders', 1)
            ->where('orders.0.id', $matchingOrder->id)
            ->where('orders.0.order_number', $matchingOrder->order_number)
            ->where('orders.0.vendor_item_count', 1)
        );
});

it('shows mixed vendor orders with the vendors own items highlighted', function () {
    [$vendorUser, $vendor] = createApprovedVendor();
    [, $otherVendor] = createApprovedVendor();
    $order = createManagedOrder(vendor: $vendor, extraVendor: $otherVendor);

    $this->actingAs($vendorUser)
        ->get(route('vendor.orders.show', ['order' => $order->id]))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('vendor/orders/show')
            ->where('order.id', $order->id)
            ->where('order.items.0.is_vendor_owned', true)
            ->where('order.items.1.is_vendor_owned', false)
            ->where('order.shipment.status', 'pending')
            ->where('order.shipment_status_options', ['vendor_preparing'])
        );
});

it('prevents vendors from viewing unrelated orders', function () {
    [$vendorUser] = createApprovedVendor();
    [, $otherVendor] = createApprovedVendor();
    $order = createManagedOrder(vendor: $otherVendor);

    $this->actingAs($vendorUser)
        ->get(route('vendor.orders.show', ['order' => $order->id]))
        ->assertForbidden();
});

it('allows vendors to advance their shipment status', function () {
    [$vendorUser, $vendor] = createApprovedVendor();
    $order = createManagedOrder(vendor: $vendor);
    $shipment = $order->shipments()->firstOrFail();

    $this->actingAs($vendorUser)
        ->from(route('vendor.orders.show', ['order' => $order->id]))
        ->patch(route('vendor.orders.shipments.status.update', ['order' => $order->id, 'shipment' => $shipment->id]), [
            'shipment_status' => 'vendor_preparing',
        ])
        ->assertRedirect(route('vendor.orders.show', ['order' => $order->id]))
        ->assertSessionHas('status', 'Shipment status updated.');

    $this->assertDatabaseHas('shipments', [
        'id' => $shipment->id,
        'status' => 'vendor_preparing',
    ]);
});

it('does not expose offline payment controls to vendors', function () {
    [$vendorUser, $vendor] = createApprovedVendor();
    $order = createManagedOrder(vendor: $vendor, paymentMethod: 'bank_transfer');

    $this->actingAs($vendorUser)
        ->get(route('vendor.orders.show', ['order' => $order->id]))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('vendor/orders/show')
            ->where('order.can_manage_offline', false)
        );
});

it('exposes UTC ISO timestamps for browser local order time rendering', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    [$vendorUser, $vendor] = createApprovedVendor();
    $order = createManagedOrder(vendor: $vendor, paymentMethod: 'bank_transfer');
    $placedAt = Carbon::parse('2026-04-05 10:00:00', 'UTC');
    $slipUploadedAt = Carbon::parse('2026-04-05 10:30:00', 'UTC');
    $shippedAt = Carbon::parse('2026-04-06 09:15:00', 'UTC');
    $deliveredAt = Carbon::parse('2026-04-07 14:45:00', 'UTC');

    $order->forceFill(['placed_at' => $placedAt])->save();

    $order->shipments()->firstOrFail()->forceFill([
        'shipped_at' => $shippedAt,
        'delivered_at' => $deliveredAt,
    ])->save();

    $order->payment()->firstOrFail()->forceFill([
        'bank_transfer_slip_path' => 'payment-proofs/slip.jpg',
        'bank_transfer_slip_original_name' => 'slip.jpg',
        'bank_transfer_slip_mime_type' => 'image/jpeg',
        'bank_transfer_slip_uploaded_at' => $slipUploadedAt,
    ])->save();

    $this->actingAs($admin)
        ->get(route('admin.orders.show', ['order' => $order->id]))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/orders/show')
            ->where('order.placed_at', $placedAt->toIso8601String())
            ->where('order.shipment.shipped_at', $shippedAt->toIso8601String())
            ->where('order.shipment.delivered_at', $deliveredAt->toIso8601String())
            ->where('order.payment_proof.uploaded_at', $slipUploadedAt->toIso8601String())
        );

    $this->actingAs($vendorUser)
        ->get(route('vendor.orders.show', ['order' => $order->id]))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('vendor/orders/show')
            ->where('order.placed_at', $placedAt->toIso8601String())
            ->where('order.payment_proof.uploaded_at', $slipUploadedAt->toIso8601String())
        );
});

/**
 * @return array{0: User, 1: Vendor}
 */
function createApprovedVendor(): array
{
    $vendorUser = User::factory()->create(['role' => 'vendor']);
    $vendor = Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
    ]);

    return [$vendorUser, $vendor];
}

function createManagedOrder(
    ?User $customer = null,
    ?Vendor $vendor = null,
    ?Vendor $extraVendor = null,
    string $paymentMethod = 'bank_transfer',
): Order {
    if ($vendor === null) {
        [, $vendor] = createApprovedVendor();
    }

    $primaryProduct = Product::factory()->for($vendor)->create([
        'status' => 'active',
        'selling_price' => '180.00',
    ]);

    $order = Order::query()->create([
        'user_id' => $customer?->id,
        'guest_name' => $customer === null ? 'Guest Buyer' : null,
        'guest_email' => $customer === null ? 'guest@example.com' : null,
        'status' => 'paid',
        'currency' => 'LKR',
        'subtotal' => $extraVendor ? '360.00' : '180.00',
        'commission_total' => $extraVendor ? '360.00' : '180.00',
        'total' => $extraVendor ? '360.00' : '180.00',
        'shipping_responsibility' => 'platform',
        'placed_at' => now(),
    ]);

    $order->items()->create([
        'product_id' => $primaryProduct->id,
        'vendor_id' => $vendor->id,
        'quantity' => 1,
        'unit_price' => '180.00',
        'commission_rate' => '100.00',
        'commission_amount' => '180.00',
        'line_total' => '180.00',
    ]);

    if ($extraVendor !== null) {
        $secondaryProduct = Product::factory()->for($extraVendor)->create([
            'status' => 'active',
            'selling_price' => '180.00',
        ]);

        $order->items()->create([
            'product_id' => $secondaryProduct->id,
            'vendor_id' => $extraVendor->id,
            'quantity' => 1,
            'unit_price' => '180.00',
            'commission_rate' => '100.00',
            'commission_amount' => '180.00',
            'line_total' => '180.00',
        ]);
    }

    $order->addresses()->createMany([
        [
            'type' => 'shipping',
            'full_name' => $customer?->name ?? 'Guest Buyer',
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
            'full_name' => $customer?->name ?? 'Guest Buyer',
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
        'status' => 'pending',
        'carrier' => 'DHL eCommerce',
        'service_level' => 'Standard',
        'tracking_number' => null,
        'package_count' => 1,
        'parcel_weight' => null,
        'weight_unit' => null,
        'parcel_length' => null,
        'parcel_width' => null,
        'parcel_height' => null,
        'parcel_dimension_unit' => null,
    ]);

    $order->payment()->create([
        'method' => $paymentMethod,
        'status' => $paymentMethod === 'cod' ? 'collection_pending' : 'pending',
        'amount' => $extraVendor ? '360.00' : '180.00',
        'currency' => 'LKR',
        'original_amount' => $extraVendor ? '360.00' : '180.00',
        'original_currency' => 'LKR',
    ]);

    return $order->fresh(['payment']);
}
