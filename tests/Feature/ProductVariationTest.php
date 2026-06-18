<?php

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductColor;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

it('creates a product with size price variations', function () {
    config()->set('commerce.commission_rate', '100.00');

    $vendorUser = User::factory()->create(['role' => 'vendor']);
    $admin = User::factory()->create(['role' => 'admin']);

    Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
        'approved_at' => now(),
        'approved_by' => $admin->id,
    ]);

    Storage::fake('public');

    $payload = [
        'product_code' => 'LC-SIZES-1',
        'name' => 'Variation Cushion',
        'description' => 'Same fabric and design with multiple sizes.',
        'vendor_price' => '2600.00',
        'category_ids' => [ProductCategory::factory()->create()->id],
        'color_ids' => [ProductColor::factory()->create()->id],
        'variations' => [
            ['label' => '16*16', 'vendor_price' => '2600.00', 'dimension_length' => '16.00', 'dimension_width' => '16.00', 'dimension_height' => '2.00'],
            ['label' => '18*18', 'vendor_price' => '2800.00', 'dimension_length' => '18.00', 'dimension_width' => '18.00', 'dimension_height' => '2.00'],
            ['label' => '20*20', 'vendor_price' => '3200.00', 'dimension_length' => '20.00', 'dimension_width' => '20.00', 'dimension_height' => '2.50'],
        ],
        'images' => [UploadedFile::fake()->image('variation-cushion.jpg')],
    ];

    $this->actingAs($vendorUser)
        ->post(route('vendor.products.store'), $payload)
        ->assertRedirect(route('vendor.products.create'));

    $product = Product::query()->where('product_code', 'LC-SIZES-1')->firstOrFail();

    expect(number_format((float) $product->vendor_price, 2, '.', ''))->toBe('2600.00')
        ->and(number_format((float) $product->selling_price, 2, '.', ''))->toBe('5200.00');

    $this->assertDatabaseHas('product_variations', [
        'product_id' => $product->id,
        'label' => '18*18',
        'vendor_price' => '2800.00',
        'selling_price' => '5600.00',
        'dimension_length' => '18.00',
        'dimension_width' => '18.00',
        'dimension_height' => '2.00',
        'sort_order' => 1,
    ]);
});

it('updates product size price variations', function () {
    config()->set('commerce.commission_rate', '100.00');

    $vendorUser = User::factory()->create(['role' => 'vendor']);
    $vendor = Vendor::factory()->for($vendorUser)->create(['status' => 'approved']);
    $product = Product::factory()->for($vendor)->create([
        'vendor_price' => '2600.00',
        'selling_price' => '5200.00',
    ]);
    $existingVariation = $product->variations()->firstOrFail();
    $category = ProductCategory::factory()->create();
    $color = ProductColor::factory()->create();

    $this->actingAs($vendorUser)
        ->patch(route('vendor.products.update', $product), [
            'product_code' => $product->product_code,
            'name' => 'Updated Variation Cushion',
            'description' => 'Updated with multiple size prices.',
            'vendor_price' => '2600.00',
            'category_ids' => [$category->id],
            'color_ids' => [$color->id],
            'variations' => [
                ['id' => $existingVariation->id, 'label' => '16*16', 'vendor_price' => '2600.00', 'dimension_length' => '16.00', 'dimension_width' => '16.00', 'dimension_height' => '2.00'],
                ['label' => '18*18', 'vendor_price' => '2800.00', 'dimension_length' => '18.00', 'dimension_width' => '18.00', 'dimension_height' => '2.00'],
            ],
        ])
        ->assertRedirect(route('vendor.products.index'));

    $product->refresh();

    expect($product->variations()->count())->toBe(2)
        ->and(number_format((float) $product->selling_price, 2, '.', ''))->toBe('5200.00');

    $this->assertDatabaseHas('product_variations', [
        'product_id' => $product->id,
        'label' => '18*18',
        'selling_price' => '5600.00',
        'dimension_length' => '18.00',
        'dimension_width' => '18.00',
        'dimension_height' => '2.00',
    ]);
});

it('exposes variation prices on the public product page for size price switching', function () {
    config()->set('commerce.commission_rate', '100.00');

    $vendor = Vendor::factory()->create(['status' => 'approved']);
    $product = Product::factory()->for($vendor)->create([
        'status' => 'active',
        'name' => 'Public Variation Cushion',
        'vendor_price' => '2600.00',
        'selling_price' => '5200.00',
    ]);
    $product->variations()->delete();
    $product->variations()->createMany([
        ['label' => '16*16', 'vendor_price' => '2600.00', 'selling_price' => '5200.00', 'dimension_length' => '16.00', 'dimension_width' => '16.00', 'dimension_height' => '2.00', 'sort_order' => 0],
        ['label' => '18*18', 'vendor_price' => '2800.00', 'selling_price' => '5600.00', 'dimension_length' => '18.00', 'dimension_width' => '18.00', 'dimension_height' => '2.00', 'sort_order' => 1],
    ]);

    $this->get(route('products.show', $product->slug))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('products/show')
            ->where('product.variations.0.label', '16*16')
            ->where('product.variations.0.selling_price', '5200.00')
            ->where('product.variations.0.dimensions.length', 16)
            ->where('product.variations.1.label', '18*18')
            ->where('product.variations.1.selling_price', '5600.00')
            ->where('product.variations.1.dimensions.length', 18)
        );
});

it('keeps cart lines separate by selected product variation', function () {
    config()->set('commerce.commission_rate', '100.00');

    $vendor = Vendor::factory()->create(['status' => 'approved']);
    $product = Product::factory()->for($vendor)->create(['status' => 'active']);
    $product->variations()->delete();
    $small = $product->variations()->create([
        'label' => '16*16',
        'vendor_price' => '2600.00',
        'selling_price' => '5200.00',
        'sort_order' => 0,
    ]);
    $large = $product->variations()->create([
        'label' => '18*18',
        'vendor_price' => '2800.00',
        'selling_price' => '5600.00',
        'sort_order' => 1,
    ]);

    $firstResponse = $this->post(route('cart.items.store'), [
        'product_id' => $product->id,
        'product_variation_id' => $small->id,
        'quantity' => 1,
        'currency' => 'LKR',
    ]);

    $guestToken = $firstResponse->getCookie('loomcraft_guest_token')?->getValue();

    $this->withCookie('loomcraft_guest_token', $guestToken)
        ->post(route('cart.items.store'), [
            'product_id' => $product->id,
            'product_variation_id' => $large->id,
            'quantity' => 1,
            'currency' => 'LKR',
        ]);

    $cart = Cart::query()->firstOrFail();

    expect($cart->items()->count())->toBe(2);

    $this->assertDatabaseHas('cart_items', [
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'product_variation_id' => $small->id,
        'product_variation_label' => '16*16',
        'unit_price' => '5200.00',
    ]);

    $this->assertDatabaseHas('cart_items', [
        'cart_id' => $cart->id,
        'product_id' => $product->id,
        'product_variation_id' => $large->id,
        'product_variation_label' => '18*18',
        'unit_price' => '5600.00',
    ]);
});

it('snapshots selected product variation details on order items', function () {
    $commissionRate = (string) config('commerce.commission_rate');
    $vendor = Vendor::factory()->create(['status' => 'approved']);
    $product = Product::factory()->for($vendor)->create(['status' => 'active']);
    $variation = $product->variations()->firstOrFail();
    $variation->forceFill([
        'label' => '20*20',
        'vendor_price' => '3200.00',
        'selling_price' => '6400.00',
    ])->save();

    $cart = Cart::query()->create([
        'guest_token' => 'guest-token',
        'currency' => 'LKR',
    ]);

    $cart->items()->create([
        'product_id' => $product->id,
        'product_variation_id' => $variation->id,
        'product_variation_label' => $variation->label,
        'quantity' => 1,
        'unit_price' => '6400.00',
    ]);

    $this->withCookie('loomcraft_guest_token', 'guest-token')
        ->post(route('checkout.store'), checkoutPayload())
        ->assertRedirect();

    $order = Order::query()->firstOrFail();

    $this->assertDatabaseHas('order_items', [
        'order_id' => $order->id,
        'product_id' => $product->id,
        'product_variation_id' => $variation->id,
        'product_variation_label' => '20*20',
        'unit_price' => '6400.00',
        'commission_rate' => $commissionRate,
        'line_total' => '6400.00',
    ]);
});

/**
 * @return array<string, string|null>
 */
function checkoutPayload(): array
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
