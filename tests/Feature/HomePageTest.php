<?php

use App\Models\Product;
use App\Models\ProductColor;
use App\Models\Suggestion;
use App\Models\User;
use App\Models\Vendor;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\get;

test('home page renders', function () {
    $vendorUser = User::factory()->create(['role' => 'vendor']);
    $vendor = Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
        'display_name' => 'Dumbara Atelier',
    ]);

    $product = Product::factory()->for($vendor)->create([
        'status' => 'active',
        'name' => 'Signed Heritage Textile',
    ]);
    $color = ProductColor::factory()->create([
        'name' => 'Beige',
        'slug' => 'beige',
    ]);
    $product->colors()->sync([$color->id]);

    $suggestion = Suggestion::factory()->for($vendorUser)->create([
        'status' => 'approved',
        'title' => 'Transparent curation support',
        'details' => 'Approval flow has helped our studio scale production.',
    ]);

    get('/')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('welcome')
            ->has('canRegister')
            ->where('atelier_ledger.active_products', 1)
            ->where('atelier_ledger.approved_feedback', 1)
            ->has('vendor_feedback', 1)
            ->where('vendor_feedback.0.id', $suggestion->id)
            ->where('vendor_feedback.0.author_name', $vendor->display_name)
            ->has('latest_products', 1)
            ->where('latest_products.0.id', $product->id)
            ->where('latest_products.0.name', $product->name)
            ->where('latest_products.0.categories', [])
            ->where('latest_products.0.colors.0.slug', 'beige')
            ->where('my_feedback', null)
        );
});

test('home page shows up to six latest active products from approved vendors', function () {
    $vendorUser = User::factory()->create(['role' => 'vendor']);
    $vendor = Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
    ]);

    $excludedVendorUser = User::factory()->create(['role' => 'vendor']);
    $excludedVendor = Vendor::factory()->for($excludedVendorUser)->create([
        'status' => 'pending',
    ]);

    $visibleProducts = [];

    foreach (range(1, 9) as $index) {
        $product = Product::factory()->for($vendor)->create([
            'status' => 'active',
            'name' => "Visible Product {$index}",
            'created_at' => now()->subMinutes(10 - $index),
            'updated_at' => now()->subMinutes(10 - $index),
        ]);

        $visibleProducts[] = $product;
    }

    Product::factory()->for($excludedVendor)->create([
        'status' => 'active',
        'name' => 'Excluded Vendor Product',
    ]);

    $response = get('/')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('welcome')
            ->has('latest_products', 6)
        );

    $latestProductIds = collect($response->viewData('page')['props']['latest_products'])
        ->pluck('id')
        ->values()
        ->all();

    $oldestVisibleProductId = $visibleProducts[0]->id;
    $latestVisibleProductId = $visibleProducts[8]->id;

    expect($latestProductIds)
        ->toHaveCount(6)
        ->toContain($latestVisibleProductId)
        ->not->toContain($oldestVisibleProductId)
        ->not->toContain($visibleProducts[1]->id)
        ->not->toContain($visibleProducts[2]->id);
});
