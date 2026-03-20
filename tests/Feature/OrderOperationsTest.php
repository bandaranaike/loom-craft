<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

it('allows admins to update order statuses including shipped', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $order = createManagedOrder();

    $this->actingAs($admin)
        ->from(route('admin.orders.show', ['order' => $order->id]))
        ->patch(route('admin.orders.status.update', ['order' => $order->id]), [
            'order_status' => 'shipped',
        ])
        ->assertRedirect(route('admin.orders.show', ['order' => $order->id]))
        ->assertSessionHas('status', 'Order status updated.');

    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'status' => 'shipped',
    ]);
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
            ->where('order.can_mark_shipped', true)
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

it('allows vendors to mark their orders as shipped', function () {
    [$vendorUser, $vendor] = createApprovedVendor();
    $order = createManagedOrder(vendor: $vendor);

    $this->actingAs($vendorUser)
        ->from(route('vendor.orders.show', ['order' => $order->id]))
        ->patch(route('vendor.orders.status.update', ['order' => $order->id]), [
            'order_status' => 'shipped',
        ])
        ->assertRedirect(route('vendor.orders.show', ['order' => $order->id]))
        ->assertSessionHas('status', 'Order marked as shipped.');

    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'status' => 'shipped',
    ]);
});

it('allows vendors to review offline payments for their orders', function () {
    [$vendorUser, $vendor] = createApprovedVendor();
    $order = createManagedOrder(vendor: $vendor, paymentMethod: 'bank_transfer');

    $this->actingAs($vendorUser)
        ->from(route('vendor.orders.show', ['order' => $order->id]))
        ->patch(route('vendor.orders.offline.update', ['order' => $order->id]), [
            'payment_status' => 'paid',
        ])
        ->assertRedirect(route('vendor.orders.show', ['order' => $order->id]))
        ->assertSessionHas('status', 'Offline payment status updated.');

    $this->assertDatabaseHas('payments', [
        'order_id' => $order->id,
        'status' => 'paid',
        'verified_by' => $vendorUser->id,
    ]);
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

    $order->payment()->create([
        'method' => $paymentMethod,
        'status' => 'pending',
        'amount' => $extraVendor ? '360.00' : '180.00',
        'currency' => 'LKR',
        'original_amount' => $extraVendor ? '360.00' : '180.00',
        'original_currency' => 'LKR',
    ]);

    return $order->fresh(['payment']);
}
