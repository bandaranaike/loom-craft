<?php

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductColor;
use App\Models\ProductMedia;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

it('allows approved vendors to view their product edit page', function () {
    $baseCurrency = (string) config('commerce.base_currency', 'LKR');
    $commissionRate = (string) config('commerce.commission_rate');
    $vendorUser = User::factory()->create(['role' => 'vendor']);
    $admin = User::factory()->create(['role' => 'admin']);

    $vendor = Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
        'approved_at' => now(),
        'approved_by' => $admin->id,
    ]);

    $product = Product::factory()->for($vendor)->create([
        'name' => 'Editable Loom Piece',
        'discount_percentage' => '18.00',
    ]);

    $this->actingAs($vendorUser)
        ->get(route('vendor.products.edit', $product))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('vendor/products/edit')
            ->where('base_currency', $baseCurrency)
            ->where('commission_rate', $commissionRate)
            ->where('product.id', $product->id)
            ->where('product.product_code', $product->product_code)
            ->where('product.name', 'Editable Loom Piece')
            ->where('product.discount_percentage', '18.00')
        );
});

it('updates vendor owned products and recalculates selling price', function () {
    $commissionRate = (string) config('commerce.commission_rate');
    $vendorUser = User::factory()->create(['role' => 'vendor']);
    $admin = User::factory()->create(['role' => 'admin']);

    $vendor = Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
        'approved_at' => now(),
        'approved_by' => $admin->id,
    ]);

    $product = Product::factory()->for($vendor)->create([
        'name' => 'Original Name',
        'vendor_price' => '100.00',
        'selling_price' => '107.00',
        'status' => 'active',
    ]);
    $initialCategory = ProductCategory::factory()->create();
    $product->categories()->sync([$initialCategory->id]);
    $updatedCategories = ProductCategory::factory()->count(2)->create();
    $initialColor = ProductColor::factory()->create();
    $product->colors()->sync([$initialColor->id]);
    $updatedColors = ProductColor::factory()->count(2)->create();

    $response = $this->actingAs($vendorUser)->patch(
        route('vendor.products.update', $product),
        [
            'product_code' => 'LC-20002',
            'name' => 'Updated Name',
            'description' => 'Updated description.',
            'vendor_price' => '200.00',
            'discount_percentage' => '25.00',
            'materials' => 'Cotton',
            'pieces_count' => 3,
            'production_time_days' => 14,
            'dimension_length' => 120,
            'dimension_width' => 40,
            'dimension_height' => 2,
            'dimension_unit' => 'cm',
            'category_ids' => $updatedCategories->pluck('id')->all(),
            'color_ids' => $updatedColors->pluck('id')->all(),
        ]
    );

    $response->assertRedirect(route('vendor.products.index'));

    $expectedSellingPrice = number_format(200 * (1 + ((float) $commissionRate / 100)), 2, '.', '');

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'product_code' => 'LC-20002',
        'slug' => 'updated-name',
        'name' => 'Updated Name',
        'vendor_price' => '200.00',
        'commission_rate' => $commissionRate,
        'selling_price' => $expectedSellingPrice,
        'discount_percentage' => '25.00',
        'status' => 'active',
    ]);
    expect($product->fresh()->categories()->pluck('product_categories.id')->all())
        ->toEqualCanonicalizing($updatedCategories->pluck('id')->all());
    expect($product->fresh()->colors()->pluck('product_colors.id')->all())
        ->toEqualCanonicalizing($updatedColors->pluck('id')->all());
});

it('prevents vendors from editing products they do not own', function () {
    $vendorUser = User::factory()->create(['role' => 'vendor']);
    $otherVendorUser = User::factory()->create(['role' => 'vendor']);
    $admin = User::factory()->create(['role' => 'admin']);

    Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
        'approved_at' => now(),
        'approved_by' => $admin->id,
    ]);

    $otherVendor = Vendor::factory()->for($otherVendorUser)->create([
        'status' => 'approved',
        'approved_at' => now(),
        'approved_by' => $admin->id,
    ]);

    $product = Product::factory()->for($otherVendor)->create();

    $this->actingAs($vendorUser)
        ->get(route('vendor.products.edit', $product))
        ->assertForbidden();

    $this->actingAs($vendorUser)
        ->patch(route('vendor.products.update', $product), [
            'product_code' => 'LC-NOT-ALLOWED',
            'name' => 'Not Allowed',
            'description' => 'Blocked.',
            'vendor_price' => '99.00',
            'category_ids' => ProductCategory::factory()->count(1)->create()->pluck('id')->all(),
            'color_ids' => ProductColor::factory()->count(1)->create()->pluck('id')->all(),
        ])
        ->assertForbidden();
});

it('requires a unique product code when updating products', function () {
    $vendorUser = User::factory()->create(['role' => 'vendor']);
    $admin = User::factory()->create(['role' => 'admin']);

    $vendor = Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
        'approved_at' => now(),
        'approved_by' => $admin->id,
    ]);

    $product = Product::factory()->for($vendor)->create([
        'product_code' => 'LC-EDIT-001',
        'status' => 'active',
    ]);
    $otherProduct = Product::factory()->for($vendor)->create([
        'product_code' => 'LC-EDIT-002',
        'status' => 'active',
    ]);

    $response = $this->actingAs($vendorUser)->patch(route('vendor.products.update', $product), [
        'product_code' => $otherProduct->product_code,
        'name' => 'Updated Name',
        'description' => 'Updated description.',
        'vendor_price' => '200.00',
        'category_ids' => ProductCategory::factory()->count(1)->create()->pluck('id')->all(),
        'color_ids' => ProductColor::factory()->count(1)->create()->pluck('id')->all(),
    ]);

    $response
        ->assertSessionHasErrors(['product_code'])
        ->assertRedirect();
});

it('allows vendors to keep the same product code when updating products', function () {
    $vendorUser = User::factory()->create(['role' => 'vendor']);
    $admin = User::factory()->create(['role' => 'admin']);

    $vendor = Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
        'approved_at' => now(),
        'approved_by' => $admin->id,
    ]);

    $product = Product::factory()->for($vendor)->create([
        'product_code' => 'LC-KEEP-001',
        'status' => 'active',
    ]);

    $response = $this->actingAs($vendorUser)->patch(route('vendor.products.update', $product), [
        'product_code' => 'LC-KEEP-001',
        'name' => 'Updated Name',
        'description' => 'Updated description.',
        'vendor_price' => '200.00',
        'category_ids' => ProductCategory::factory()->count(1)->create()->pluck('id')->all(),
        'color_ids' => ProductColor::factory()->count(1)->create()->pluck('id')->all(),
    ]);

    $response->assertRedirect(route('vendor.products.index'));

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'product_code' => 'LC-KEEP-001',
    ]);
});

it('allows vendors to upload additional product images', function () {
    Storage::fake('public');

    $vendorUser = User::factory()->create(['role' => 'vendor']);
    $admin = User::factory()->create(['role' => 'admin']);

    $vendor = Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
        'approved_at' => now(),
        'approved_by' => $admin->id,
    ]);

    $product = Product::factory()->for($vendor)->create();

    $response = $this->actingAs($vendorUser)->post(route('vendor.products.images.store', $product), [
        'images' => [
            UploadedFile::fake()->image('fresh-1.jpg'),
            UploadedFile::fake()->image('fresh-2.jpg'),
        ],
    ]);

    $response->assertRedirect(route('vendor.products.edit', $product));

    $this->assertSame(2, $product->media()->where('type', 'image')->count());

    $paths = $product->media()->where('type', 'image')->pluck('path')->all();
    foreach ($paths as $path) {
        Storage::disk('public')->assertExists($path);
    }
});

it('allows vendors to delete their product image', function () {
    Storage::fake('public');

    $vendorUser = User::factory()->create(['role' => 'vendor']);
    $admin = User::factory()->create(['role' => 'admin']);

    $vendor = Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
        'approved_at' => now(),
        'approved_by' => $admin->id,
    ]);

    $product = Product::factory()->for($vendor)->create();
    $path = Storage::disk('public')->putFile(
        'products/images',
        UploadedFile::fake()->image('existing.jpg')
    );

    $image = ProductMedia::query()->create([
        'product_id' => $product->id,
        'type' => 'image',
        'path' => $path,
        'sort_order' => 0,
    ]);

    $response = $this->actingAs($vendorUser)->delete(
        route('vendor.products.images.destroy', ['product' => $product->id, 'image' => $image->id])
    );

    $response->assertRedirect(route('vendor.products.edit', $product));

    $this->assertDatabaseMissing('product_media', [
        'id' => $image->id,
    ]);
    Storage::disk('public')->assertMissing($path);
});

it('prevents vendors from managing images on products they do not own', function () {
    Storage::fake('public');

    $vendorUser = User::factory()->create(['role' => 'vendor']);
    $otherVendorUser = User::factory()->create(['role' => 'vendor']);
    $admin = User::factory()->create(['role' => 'admin']);

    Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
        'approved_at' => now(),
        'approved_by' => $admin->id,
    ]);

    $otherVendor = Vendor::factory()->for($otherVendorUser)->create([
        'status' => 'approved',
        'approved_at' => now(),
        'approved_by' => $admin->id,
    ]);

    $product = Product::factory()->for($otherVendor)->create();
    $path = Storage::disk('public')->putFile(
        'products/images',
        UploadedFile::fake()->image('foreign.jpg')
    );

    $image = ProductMedia::query()->create([
        'product_id' => $product->id,
        'type' => 'image',
        'path' => $path,
        'sort_order' => 0,
    ]);

    $this->actingAs($vendorUser)
        ->post(route('vendor.products.images.store', $product), [
            'images' => [UploadedFile::fake()->image('blocked.jpg')],
        ])
        ->assertForbidden();

    $this->actingAs($vendorUser)
        ->delete(route('vendor.products.images.destroy', ['product' => $product->id, 'image' => $image->id]))
        ->assertForbidden();
});
