<?php

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductColor;
use App\Models\User;
use App\Models\Vendor;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\get;

test('home page renders', function () {
    $vendor = Vendor::factory()->create([
        'status' => 'approved',
    ]);
    $category = ProductCategory::factory()->create([
        'name' => 'Cushion Covers',
        'slug' => 'cushion-covers',
        'description' => 'Soft architectural pieces for refined rooms.',
        'sort_order' => 1,
    ]);

    $product = Product::factory()->for($vendor)->create([
        'status' => 'active',
        'name' => 'Signed Heritage Textile',
    ]);
    $product->categories()->sync([$category->id]);
    $color = ProductColor::factory()->create([
        'name' => 'Beige',
        'slug' => 'beige',
    ]);
    $product->colors()->sync([$color->id]);

    get('/')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('welcome')
            ->has('canRegister')
            ->has('category_sections', 1)
            ->where('category_sections.0.id', $category->id)
            ->where('category_sections.0.name', 'Cushion Covers')
            ->where('category_sections.0.slug', 'cushion-covers')
            ->where('category_sections.0.description', 'Soft architectural pieces for refined rooms.')
            ->has('category_sections.0.products', 1)
            ->where('category_sections.0.products.0.id', $product->id)
            ->where('category_sections.0.products.0.name', $product->name)
            ->where('category_sections.0.products.0.categories.0.slug', 'cushion-covers')
            ->where('category_sections.0.products.0.colors.0.slug', 'beige')
        );
});

test('home page shows up to five categories with three active products from approved vendors', function () {
    $vendorUser = User::factory()->create(['role' => 'vendor']);
    $vendor = Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
    ]);

    $excludedVendorUser = User::factory()->create(['role' => 'vendor']);
    $excludedVendor = Vendor::factory()->for($excludedVendorUser)->create([
        'status' => 'pending',
    ]);

    $visibleCategoryIds = [];

    foreach (range(1, 6) as $categoryIndex) {
        $category = ProductCategory::factory()->create([
            'name' => "Visible Category {$categoryIndex}",
            'slug' => "visible-category-{$categoryIndex}",
            'sort_order' => $categoryIndex,
        ]);

        $visibleCategoryIds[] = $category->id;

        foreach (range(1, 4) as $productIndex) {
            $product = Product::factory()->for($vendor)->create([
                'status' => 'active',
                'name' => "Visible Product {$categoryIndex}-{$productIndex}",
                'created_at' => now()->subMinutes($productIndex),
                'updated_at' => now()->subMinutes($productIndex),
            ]);

            $product->categories()->sync([$category->id]);
        }
    }

    $pendingVendorCategory = ProductCategory::factory()->create([
        'name' => 'Pending Vendor Category',
        'slug' => 'pending-vendor-category',
        'sort_order' => 0,
    ]);
    $pendingVendorProduct = Product::factory()->for($excludedVendor)->create([
        'status' => 'active',
        'name' => 'Excluded Vendor Product',
    ]);
    $pendingVendorProduct->categories()->sync([$pendingVendorCategory->id]);

    $response = get('/')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('welcome')
            ->has('category_sections', 5)
            ->has('category_sections.0.products', 3)
            ->has('category_sections.1.products', 3)
            ->has('category_sections.2.products', 3)
            ->has('category_sections.3.products', 3)
            ->has('category_sections.4.products', 3)
        );

    $homeCategoryIds = collect($response->viewData('page')['props']['category_sections'])
        ->pluck('id')
        ->values()
        ->all();

    expect($homeCategoryIds)
        ->toBe(array_slice($visibleCategoryIds, 0, 5))
        ->not->toContain($pendingVendorCategory->id);

    collect($response->viewData('page')['props']['category_sections'])
        ->each(function (array $categorySection): void {
            expect($categorySection['products'])
                ->toHaveCount(3);
        });
});
