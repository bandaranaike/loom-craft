<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

it('allows admins to update offline payment and order statuses', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $order = createOfflineOrder(paymentMethod: 'bank_transfer');

    $this->actingAs($admin)
        ->from(route('admin.orders.show', ['order' => $order->id]))
        ->patch(route('admin.orders.offline.update', ['order' => $order->id]), [
            'payment_status' => 'paid',
        ])
        ->assertRedirect(route('admin.orders.show', ['order' => $order->id]))
        ->assertSessionHas('status', 'Offline payment status updated.');

    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'status' => 'pending',
        'shipping_responsibility' => 'platform',
    ]);

    $this->assertDatabaseHas('payments', [
        'order_id' => $order->id,
        'method' => 'bank_transfer',
        'status' => 'paid',
        'verified_by' => $admin->id,
    ]);

    expect($order->payment()->first()?->verified_at)->not->toBeNull();
});

it('prevents non-admins from updating offline payment and order statuses', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $order = createOfflineOrder(user: $customer, paymentMethod: 'cod');

    $this->actingAs($customer)
        ->patch(route('admin.orders.offline.update', ['order' => $order->id]), [
            'payment_status' => 'paid',
        ])
        ->assertForbidden();

    $this->assertDatabaseHas('payments', [
        'order_id' => $order->id,
        'status' => 'pending',
        'verified_by' => null,
    ]);
});

it('stores the final bank transfer slip for guest orders', function () {
    Storage::fake('public');

    $order = createOfflineOrder(paymentMethod: 'bank_transfer');
    $file = UploadedFile::fake()->create('final-slip.jpg', 12, 'image/jpeg');

    $this->from(route('orders.confirmation', ['order' => $order->public_id]))
        ->withSession(['guest_order_id' => $order->id])
        ->post(route('orders.bank-transfer-slip.store', ['order' => $order->public_id]), [
            'slip' => $file,
        ])
        ->assertRedirect(route('orders.confirmation', ['order' => $order->public_id]))
        ->assertSessionHas('status', 'Bank transfer slip uploaded successfully.');

    $payment = $order->payment()->firstOrFail()->fresh();

    expect($payment->bank_transfer_slip_original_name)->toBe('final-slip.jpg')
        ->and($payment->bank_transfer_slip_mime_type)->toBe('image/jpeg')
        ->and($payment->bank_transfer_slip_uploaded_at)->not->toBeNull()
        ->and($payment->bank_transfer_slip_path)->not->toBeNull();

    Storage::disk('public')->assertExists($payment->bank_transfer_slip_path);
});

it('rejects invalid bank transfer slip file types', function () {
    Storage::fake('public');

    $order = createOfflineOrder(paymentMethod: 'bank_transfer');

    $this->from(route('orders.confirmation', ['order' => $order->public_id]))
        ->withSession(['guest_order_id' => $order->id])
        ->post(route('orders.bank-transfer-slip.store', ['order' => $order->public_id]), [
            'slip' => UploadedFile::fake()->create('notes.txt', 5, 'text/plain'),
        ])
        ->assertRedirect(route('orders.confirmation', ['order' => $order->public_id]))
        ->assertSessionHasErrors(['slip']);

    $this->assertDatabaseHas('payments', [
        'order_id' => $order->id,
        'bank_transfer_slip_path' => null,
    ]);
});

it('shows order details and uploaded proof on the guest confirmation page', function () {
    Storage::fake('public');

    $order = createOfflineOrder(paymentMethod: 'bank_transfer');
    $payment = $order->payment()->firstOrFail();
    $path = UploadedFile::fake()->create('proof.pdf', 12, 'application/pdf')
        ->store('bank-transfer-slips', 'public');

    $payment->update([
        'bank_transfer_slip_path' => $path,
        'bank_transfer_slip_original_name' => 'proof.pdf',
        'bank_transfer_slip_mime_type' => 'application/pdf',
        'bank_transfer_slip_uploaded_at' => now(),
    ]);

    $this->withSession(['guest_order_id' => $order->id])
        ->get(route('orders.confirmation', ['order' => $order->public_id]))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('orders/confirmation')
            ->where('order.id', $order->id)
            ->where('order.public_id', $order->public_id)
            ->where('order.total', '180.00')
            ->where('order.currency', 'LKR')
            ->where('order.payment_method', 'bank_transfer')
            ->where('order.status', 'pending')
            ->where('order.payment_proof.original_name', 'proof.pdf')
        );
});

it('shows offline review options and payment proof on the admin order page', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => 'admin']);
    $order = createOfflineOrder(paymentMethod: 'bank_transfer');
    $payment = $order->payment()->firstOrFail();
    $path = UploadedFile::fake()->create('review-slip.png', 12, 'image/png')
        ->store('bank-transfer-slips', 'public');

    $payment->update([
        'bank_transfer_slip_path' => $path,
        'bank_transfer_slip_original_name' => 'review-slip.png',
        'bank_transfer_slip_mime_type' => 'image/png',
        'bank_transfer_slip_uploaded_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.orders.show', ['order' => $order->id]))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/orders/show')
            ->where('order.id', $order->id)
            ->where('order.can_manage_offline', true)
            ->where('order.can_delete', true)
            ->where('order.payment_status_options', ['paid', 'failed'])
            ->where('order.order_status_options', ['pending', 'paid', 'confirmed', 'shipped', 'delivered', 'cancelled'])
            ->where('order.payment_proof.original_name', 'review-slip.png')
        );
});

it('shows uploaded proof for cod orders on the admin order page', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => 'admin']);
    $order = createOfflineOrder(paymentMethod: 'cod');
    $payment = $order->payment()->firstOrFail();
    $path = UploadedFile::fake()->create('cod-proof.jpg', 12, 'image/jpeg')
        ->store('bank-transfer-slips', 'public');

    $payment->update([
        'bank_transfer_slip_path' => $path,
        'bank_transfer_slip_original_name' => 'cod-proof.jpg',
        'bank_transfer_slip_mime_type' => 'image/jpeg',
        'bank_transfer_slip_uploaded_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.orders.show', ['order' => $order->id]))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/orders/show')
            ->where('order.payment_method', 'cod')
            ->where('order.can_manage_offline', true)
            ->where('order.payment_proof.original_name', 'cod-proof.jpg')
        );
});

function createOfflineOrder(?User $user = null, string $paymentMethod = 'bank_transfer'): Order
{
    $vendor = Vendor::factory()->create([
        'status' => 'approved',
    ]);

    $product = Product::factory()->for($vendor)->create([
        'status' => 'active',
        'selling_price' => '180.00',
    ]);

    $order = Order::query()->create([
        'user_id' => $user?->id,
        'guest_name' => $user === null ? 'Guest Buyer' : null,
        'guest_email' => $user === null ? 'guest@example.com' : null,
        'status' => 'pending',
        'currency' => 'LKR',
        'subtotal' => '180.00',
        'commission_total' => '180.00',
        'total' => '180.00',
        'shipping_responsibility' => 'platform',
        'placed_at' => now(),
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

    $order->payment()->create([
        'method' => $paymentMethod,
        'status' => 'pending',
        'amount' => '180.00',
        'currency' => 'LKR',
        'original_amount' => '180.00',
        'original_currency' => 'LKR',
    ]);

    return $order->fresh(['payment']);
}
