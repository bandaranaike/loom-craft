<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;

it('issues a sanctum token for approved vendor users', function () {
    [$vendorUser, $vendor] = createApprovedVendorApiUser();

    $this->postJson('/api/v1/login', [
        'email' => $vendorUser->email,
        'password' => 'password',
        'device_name' => 'Pixel 10',
    ])->assertOk()
        ->assertJsonPath('user.id', $vendorUser->id)
        ->assertJsonPath('user.role', 'vendor')
        ->assertJsonPath('user.vendor_id', $vendor->id)
        ->assertJsonStructure([
            'token',
            'user' => ['id', 'name', 'role', 'vendor_id'],
        ]);

    expect($vendorUser->fresh()->tokens)->toHaveCount(1);
});

it('rejects customer accounts from the mobile api login', function () {
    $customer = User::factory()->create(['role' => 'customer']);

    $this->postJson('/api/v1/login', [
        'email' => $customer->email,
        'password' => 'password',
    ])->assertForbidden();
});

it('lists all orders for admin mobile api users', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create(['role' => 'customer', 'name' => 'Jane Smith']);
    $firstOrder = createMobileManagedOrder(customer: $customer);
    $secondOrder = createMobileManagedOrder();

    Sanctum::actingAs($admin, ['orders:read']);

    $this->getJson('/api/v1/orders')
        ->assertOk()
        ->assertJsonCount(2)
        ->assertJsonFragment(['id' => $firstOrder->id, 'customer_name' => 'Jane Smith'])
        ->assertJsonPath('0.created_at', 'Apr 05, 2026 10:00 AM')
        ->assertJsonFragment(['id' => $secondOrder->id]);
});

it('lists only vendor-visible order summaries without customer or global totals', function () {
    [$vendorUser, $vendor] = createApprovedVendorApiUser();
    [, $otherVendor] = createApprovedVendorApiUser();
    $matchingOrder = createMobileManagedOrder(vendor: $vendor, customer: User::factory()->create(['role' => 'customer']));
    createMobileManagedOrder(vendor: $otherVendor);

    Sanctum::actingAs($vendorUser, ['orders:read']);

    $this->getJson('/api/v1/orders')
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.id', $matchingOrder->id)
        ->assertJsonPath('0.vendor_items_total', 180)
        ->assertJsonPath('0.items_count', 1)
        ->assertJsonPath('0.created_at', 'Apr 05, 2026 10:00 AM')
        ->assertJsonMissingPath('0.customer_name')
        ->assertJsonMissingPath('0.total');
});

it('returns the mobile vendor order detail shape', function () {
    [$vendorUser, $vendor] = createApprovedVendorApiUser();
    $customer = User::factory()->create([
        'role' => 'customer',
        'name' => 'Jane Smith',
        'email' => 'jane@example.com',
    ]);
    $order = createMobileManagedOrder(customer: $customer, vendor: $vendor);

    Sanctum::actingAs($vendorUser, ['orders:read']);

    $this->getJson("/api/v1/orders/{$order->id}")
        ->assertOk()
        ->assertJsonPath('id', $order->id)
        ->assertJsonPath('public_id', $order->public_id)
        ->assertJsonPath('currency', 'LKR')
        ->assertJsonPath('total', 180)
        ->assertJsonPath('created_at', 'Apr 05, 2026 10:00 AM')
        ->assertJsonPath('items.0.product_name', $order->items()->first()->product->name)
        ->assertJsonPath('items.0.status', $order->status)
        ->assertJsonPath('items.0.currency', 'LKR')
        ->assertJsonPath('items.0.image_url', url('/storage/products/mobile-api-scarf.jpg'))
        ->assertJsonPath('items.0.product_media.0.path', 'products/mobile-api-scarf.jpg')
        ->assertJsonMissingPath('customer')
        ->assertJsonMissingPath('addresses')
        ->assertJsonMissingPath('payment')
        ->assertJsonMissingPath('subtotal')
        ->assertJsonMissingPath('commission_total')
        ->assertJsonMissingPath('shipping_responsibility')
        ->assertJsonMissingPath('shipments');
});

it('returns the mobile admin order detail shape', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create([
        'role' => 'customer',
        'name' => 'Jane Smith',
        'email' => 'jane@example.com',
    ]);
    [$vendorUser, $vendor] = createApprovedVendorApiUser();
    $order = createMobileManagedOrder(customer: $customer, vendor: $vendor);

    Sanctum::actingAs($admin, ['orders:read']);

    $this->getJson("/api/v1/orders/{$order->id}")
        ->assertOk()
        ->assertJsonPath('id', $order->id)
        ->assertJsonPath('public_id', $order->public_id)
        ->assertJsonPath('created_at', 'Apr 05, 2026 10:00 AM')
        ->assertJsonPath('customer_name', 'Jane Smith')
        ->assertJsonPath('addresses.0.city', 'Kandy')
        ->assertJsonPath('addresses.0.phone', '0770000000')
        ->assertJsonPath('items.0.vendor_name', $vendorUser->vendor->display_name)
        ->assertJsonPath('items.0.status', $order->status)
        ->assertJsonPath('items.0.currency', 'LKR')
        ->assertJsonPath('items.0.image_url', url('/storage/products/mobile-api-scarf.jpg'))
        ->assertJsonMissingPath('customer')
        ->assertJsonMissingPath('payment')
        ->assertJsonMissingPath('shipments')
        ->assertJsonMissingPath('subtotal')
        ->assertJsonMissingPath('commission_total')
        ->assertJsonMissingPath('shipping_responsibility');
});

it('allows admins to update order statuses through the mobile api', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $order = createMobileManagedOrder(status: 'paid');

    Sanctum::actingAs($admin, ['orders:update']);

    $this->patchJson("/api/v1/orders/{$order->id}/status", [
        'status' => 'delivered',
    ])->assertOk()
        ->assertJsonPath('order.status', 'delivered');

    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'status' => 'delivered',
    ]);
});

it('allows vendors to mark confirmed orders as shipped only', function () {
    [$vendorUser, $vendor] = createApprovedVendorApiUser();
    $order = createMobileManagedOrder(vendor: $vendor, status: 'confirmed');

    Sanctum::actingAs($vendorUser, ['orders:update']);

    $this->patchJson("/api/v1/orders/{$order->id}/status", [
        'status' => 'shipped',
    ])->assertOk()
        ->assertJsonPath('order.status', 'shipped');

    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'status' => 'shipped',
    ]);
});

it('rejects vendor status updates from unsupported starting states', function () {
    [$vendorUser, $vendor] = createApprovedVendorApiUser();
    $order = createMobileManagedOrder(vendor: $vendor, status: 'pending');

    Sanctum::actingAs($vendorUser, ['orders:update']);

    $this->patchJson("/api/v1/orders/{$order->id}/status", [
        'status' => 'shipped',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors('status');
});

it('registers mobile notification tokens for authenticated users', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Sanctum::actingAs($admin, ['notifications:register']);

    $this->postJson('/api/v1/notifications/register', [
        'fcm_token' => 'test-fcm-token',
        'platform' => 'android',
    ])->assertOk()
        ->assertJsonPath('data.platform', 'android')
        ->assertJsonPath('data.last_used_at', now()->format('M d, Y g:i A'));

    $this->assertDatabaseHas('mobile_notification_tokens', [
        'user_id' => $admin->id,
        'fcm_token' => 'test-fcm-token',
        'platform' => 'android',
    ]);
});

it('returns dense sticker payload data for admins', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    [$vendorUser, $vendor] = createApprovedVendorApiUser();
    $order = createMobileManagedOrder(vendor: $vendor, customer: $vendorUser);

    Sanctum::actingAs($admin, ['stickers:read']);

    $this->getJson("/api/v1/admin/orders/{$order->id}/sticker-data")
        ->assertOk()
        ->assertJsonPath('id', $order->id)
        ->assertJsonPath('customer_name', $vendorUser->name)
        ->assertJsonPath('shipping_address.city', 'Kandy')
        ->assertJsonPath('products.0.quantity', 1);
});

/**
 * @return array{0: User, 1: Vendor}
 */
function createApprovedVendorApiUser(): array
{
    $vendorUser = User::factory()->create(['role' => 'vendor']);
    $vendor = Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
        'approved_at' => now(),
    ]);

    return [$vendorUser, $vendor];
}

function createMobileManagedOrder(
    ?User $customer = null,
    ?Vendor $vendor = null,
    string $status = 'paid',
): Order {
    if ($vendor === null) {
        [, $vendor] = createApprovedVendorApiUser();
    }

    $product = Product::factory()->for($vendor)->create([
        'status' => 'active',
        'selling_price' => '180.00',
        'name' => 'Mobile API Scarf',
        'product_code' => fake()->unique()->bothify('MOBILE-###'),
    ]);

    $product->media()->create([
        'type' => 'image',
        'path' => 'products/mobile-api-scarf.jpg',
        'alt_text' => 'Mobile API Scarf',
        'sort_order' => 0,
    ]);

    $order = Order::query()->create([
        'user_id' => $customer?->id,
        'guest_name' => $customer === null ? 'Guest Buyer' : null,
        'guest_email' => $customer === null ? 'guest@example.com' : null,
        'status' => $status,
        'currency' => 'LKR',
        'subtotal' => '180.00',
        'commission_total' => '180.00',
        'total' => '180.00',
        'shipping_responsibility' => 'platform',
        'placed_at' => Carbon::parse('2026-04-05 10:00:00'),
    ]);

    $order->forceFill([
        'created_at' => Carbon::parse('2026-04-05 10:00:00'),
        'updated_at' => Carbon::parse('2026-04-05 10:00:00'),
    ])->saveQuietly();

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
        'carrier' => null,
        'tracking_number' => null,
        'shipped_at' => null,
        'delivered_at' => null,
    ]);

    $order->payment()->create([
        'method' => 'bank_transfer',
        'status' => 'pending',
        'amount' => '180.00',
        'currency' => 'LKR',
        'original_amount' => '180.00',
        'original_currency' => 'LKR',
    ]);

    return $order->fresh(['items.product.media', 'addresses', 'shipments', 'payment']);
}
