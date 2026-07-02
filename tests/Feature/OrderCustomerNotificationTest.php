<?php

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use App\Notifications\Channels\DialogEsmsChannel;
use App\Notifications\OrderCustomerNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

it('notifies the customer by email and sms when a guest order is placed', function () {
    Notification::fake();

    createCheckoutCartForNotification();

    $this->withCookie('loomcraft_guest_token', 'guest-token')
        ->post(route('checkout.store'), checkoutPayloadForNotification())
        ->assertRedirect();

    $order = Order::query()->firstOrFail();

    Notification::assertSentOnDemand(
        OrderCustomerNotification::class,
        function (OrderCustomerNotification $notification, array $channels, AnonymousNotifiable $notifiable) use ($order): bool {
            return $notification->event === 'order_placed'
                && $notification->orderNumber === $order->order_number
                && in_array('mail', $channels, true)
                && in_array(DialogEsmsChannel::class, $channels, true)
                && $notifiable->routes['mail'] === 'patron@example.com'
                && $notifiable->routes['dialog_esms'] === '0770000000';
        },
    );
});

it('notifies the customer for selected customer-facing status changes', function () {
    Notification::fake();

    $admin = User::factory()->create(['role' => 'admin']);
    $order = createOrderForCustomerNotification(status: 'paid');

    $this->actingAs($admin)
        ->patch(route('admin.orders.status.update', ['order' => $order->id]), [
            'order_status' => 'confirmed',
        ])
        ->assertRedirect();

    Notification::assertSentOnDemand(
        OrderCustomerNotification::class,
        fn (OrderCustomerNotification $notification, array $channels): bool => $notification->event === 'order_confirmed'
            && $notification->orderNumber === $order->fresh()->order_number
            && in_array('mail', $channels, true)
            && in_array(DialogEsmsChannel::class, $channels, true),
    );
});

it('does not notify the customer for internal shipment preparation changes', function () {
    Notification::fake();

    $vendorUser = User::factory()->create(['role' => 'vendor']);
    $vendor = Vendor::factory()->for($vendorUser)->create(['status' => 'approved']);
    $order = createOrderForCustomerNotification(status: 'paid', vendor: $vendor);
    $shipment = $order->shipments()->firstOrFail();

    $this->actingAs($vendorUser)
        ->patch(route('vendor.orders.shipments.status.update', ['order' => $order->id, 'shipment' => $shipment->id]), [
            'shipment_status' => 'vendor_preparing',
        ])
        ->assertRedirect();

    Notification::assertNothingSent();
});

function createCheckoutCartForNotification(): void
{
    $vendorUser = User::factory()->create(['role' => 'vendor']);
    $vendor = Vendor::factory()->for($vendorUser)->create(['status' => 'approved']);
    $product = Product::factory()->for($vendor)->create([
        'status' => 'active',
        'selling_price' => '180.00',
    ]);

    Cart::query()->create([
        'guest_token' => 'guest-token',
        'currency' => 'LKR',
    ])->items()->create([
        'product_id' => $product->id,
        'quantity' => 1,
        'unit_price' => '180.00',
    ]);
}

/**
 * @return array<string, string|null>
 */
function checkoutPayloadForNotification(): array
{
    return [
        'guest_name' => 'Heritage Patron',
        'guest_email' => 'patron@example.com',
        'currency' => 'LKR',
        'shipping_responsibility' => 'platform',
        'payment_method' => 'cod',
        'shipping_full_name' => 'Heritage Patron',
        'shipping_line1' => '1 Loom Street',
        'shipping_line2' => 'Suite 2',
        'shipping_city' => 'Kandy',
        'shipping_region' => 'Central',
        'shipping_postal_code' => '20000',
        'shipping_country_code' => 'LK',
        'shipping_phone' => '0770000000',
        'billing_full_name' => 'Heritage Patron',
        'billing_line1' => '1 Loom Street',
        'billing_line2' => null,
        'billing_city' => 'Kandy',
        'billing_region' => 'Central',
        'billing_postal_code' => '20000',
        'billing_country_code' => 'LK',
        'billing_phone' => '0770000000',
    ];
}

function createOrderForCustomerNotification(string $status, ?Vendor $vendor = null): Order
{
    if ($vendor === null) {
        $vendorUser = User::factory()->create(['role' => 'vendor']);
        $vendor = Vendor::factory()->for($vendorUser)->create(['status' => 'approved']);
    }

    $product = Product::factory()->for($vendor)->create([
        'status' => 'active',
        'selling_price' => '180.00',
    ]);

    $order = Order::query()->create([
        'guest_name' => 'Guest Buyer',
        'guest_email' => 'guest@example.com',
        'status' => $status,
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
        'method' => 'bank_transfer',
        'status' => 'pending',
        'amount' => '180.00',
        'currency' => 'LKR',
        'original_amount' => '180.00',
        'original_currency' => 'LKR',
    ]);

    $order->shipments()->create([
        'vendor_id' => $vendor->id,
        'responsibility' => 'platform',
        'status' => 'pending',
        'package_count' => 1,
    ]);

    return $order->fresh();
}
