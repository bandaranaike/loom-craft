<?php

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductColor;
use App\Models\Vendor;
use Inertia\Testing\AssertableInertia as Assert;

test('guests can view approved active products', function () {
    $approvedVendor = Vendor::factory()->create([
        'status' => 'approved',
        'display_name' => 'Heritage Loom Atelier',
    ]);
    $activeProduct = Product::factory()->create([
        'vendor_id' => $approvedVendor->id,
        'status' => 'active',
        'name' => 'Dumbara Signature Weave',
    ]);
    $category = ProductCategory::factory()->create([
        'name' => 'Wall Hangers',
        'slug' => 'wall-hangers',
    ]);
    $activeProduct->categories()->sync([$category->id]);
    $color = ProductColor::factory()->create([
        'name' => 'Beige',
        'slug' => 'beige',
    ]);
    $activeProduct->colors()->sync([$color->id]);
    Product::factory()->create([
        'vendor_id' => $approvedVendor->id,
        'status' => 'pending_review',
    ]);

    $response = $this->get(route('products.index'));

    $response
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('products/index')
            ->where('products.0.id', $activeProduct->id)
            ->where('products.0.name', $activeProduct->name)
            ->where('products.0.vendor_name', $approvedVendor->display_name)
            ->where('products.0.categories.0.slug', 'wall-hangers')
            ->where('products.0.colors.0.slug', 'beige')
        );
});

test('products from unapproved vendors are not visible', function () {
    $pendingVendor = Vendor::factory()->create([
        'status' => 'pending',
    ]);
    $product = Product::factory()->create([
        'vendor_id' => $pendingVendor->id,
        'status' => 'active',
    ]);

    $response = $this->get(route('products.index'));

    $response
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('products/index')
            ->where('products', [])
        );

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'status' => 'active',
    ]);
});

test('users can filter public products by category slug', function () {
    $approvedVendor = Vendor::factory()->create([
        'status' => 'approved',
    ]);
    $matchingCategory = ProductCategory::factory()->create([
        'name' => 'Pillow Covers',
        'slug' => 'pillow-covers',
    ]);
    $otherCategory = ProductCategory::factory()->create([
        'name' => 'Wall Hangers',
        'slug' => 'wall-hangers',
    ]);

    $matchingProduct = Product::factory()->create([
        'vendor_id' => $approvedVendor->id,
        'status' => 'active',
        'name' => 'Forest Pillow Cover',
    ]);
    $matchingProduct->categories()->sync([$matchingCategory->id]);

    $otherProduct = Product::factory()->create([
        'vendor_id' => $approvedVendor->id,
        'status' => 'active',
        'name' => 'Textile Wall Hanging',
    ]);
    $otherProduct->categories()->sync([$otherCategory->id]);

    $response = $this->get(route('products.index', [
        'category' => 'pillow-covers',
    ]));

    $response
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('products/index')
            ->has('products', 1)
            ->where('products.0.id', $matchingProduct->id)
            ->where('selected_category', 'pillow-covers')
        );
});

test('users can filter public products by colors', function () {
    $approvedVendor = Vendor::factory()->create([
        'status' => 'approved',
    ]);
    $beige = ProductColor::factory()->create([
        'name' => 'Beige',
        'slug' => 'beige',
    ]);
    $black = ProductColor::factory()->create([
        'name' => 'Black',
        'slug' => 'black',
    ]);

    $beigeProduct = Product::factory()->create([
        'vendor_id' => $approvedVendor->id,
        'status' => 'active',
        'name' => 'Beige Loom Pillow',
    ]);
    $beigeProduct->colors()->sync([$beige->id]);

    $blackProduct = Product::factory()->create([
        'vendor_id' => $approvedVendor->id,
        'status' => 'active',
        'name' => 'Black Loom Pillow',
    ]);
    $blackProduct->colors()->sync([$black->id]);

    $response = $this->get(route('products.index', [
        'colors' => ['beige'],
    ]));

    $response
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('products/index')
            ->has('products', 1)
            ->where('products.0.id', $beigeProduct->id)
            ->where('selected_colors', ['beige'])
        );
});

test('users can filter public products by vendor slug', function () {
    $matchingVendor = Vendor::factory()->create([
        'status' => 'approved',
        'slug' => 'heritage-loom',
    ]);
    $otherVendor = Vendor::factory()->create([
        'status' => 'approved',
        'slug' => 'mountain-weavers',
    ]);

    $matchingProduct = Product::factory()->create([
        'vendor_id' => $matchingVendor->id,
        'status' => 'active',
        'name' => 'Heritage Loom Runner',
    ]);

    Product::factory()->create([
        'vendor_id' => $otherVendor->id,
        'status' => 'active',
        'name' => 'Mountain Weavers Runner',
    ]);

    $response = $this->get(route('products.index', [
        'vendor' => 'heritage-loom',
    ]));

    $response
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('products/index')
            ->has('products', 1)
            ->where('products.0.id', $matchingProduct->id)
            ->where('selected_vendor', 'heritage-loom')
        );
});

test('users can filter public products by price range', function () {
    $approvedVendor = Vendor::factory()->create([
        'status' => 'approved',
    ]);

    Product::factory()->create([
        'vendor_id' => $approvedVendor->id,
        'status' => 'active',
        'selling_price' => 120.00,
        'name' => 'Entry Textile',
    ]);

    $matchingProduct = Product::factory()->create([
        'vendor_id' => $approvedVendor->id,
        'status' => 'active',
        'selling_price' => 500.00,
        'name' => 'Mid Textile',
    ]);

    Product::factory()->create([
        'vendor_id' => $approvedVendor->id,
        'status' => 'active',
        'selling_price' => 980.00,
        'name' => 'Premium Textile',
    ]);

    $response = $this->get(route('products.index', [
        'min_price' => 200,
        'max_price' => 700,
    ]));

    $response
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('products/index')
            ->has('products', 1)
            ->where('products.0.id', $matchingProduct->id)
            ->where('min_price', '200')
            ->where('max_price', '700')
        );
});

test('users can combine filters and receive intersection results', function () {
    $matchingVendor = Vendor::factory()->create([
        'status' => 'approved',
        'slug' => 'artisan-vendor',
    ]);
    $otherVendor = Vendor::factory()->create([
        'status' => 'approved',
        'slug' => 'other-vendor',
    ]);

    $category = ProductCategory::factory()->create([
        'name' => 'Runners',
        'slug' => 'runners',
    ]);
    $blue = ProductColor::factory()->create([
        'name' => 'Blue',
        'slug' => 'blue',
    ]);
    $red = ProductColor::factory()->create([
        'name' => 'Red',
        'slug' => 'red',
    ]);

    $matchingProduct = Product::factory()->create([
        'vendor_id' => $matchingVendor->id,
        'status' => 'active',
        'name' => 'Blue Runner',
        'selling_price' => 450.00,
    ]);
    $matchingProduct->categories()->sync([$category->id]);
    $matchingProduct->colors()->sync([$blue->id]);

    $wrongColorProduct = Product::factory()->create([
        'vendor_id' => $matchingVendor->id,
        'status' => 'active',
        'name' => 'Red Runner',
        'selling_price' => 450.00,
    ]);
    $wrongColorProduct->categories()->sync([$category->id]);
    $wrongColorProduct->colors()->sync([$red->id]);

    $wrongVendorProduct = Product::factory()->create([
        'vendor_id' => $otherVendor->id,
        'status' => 'active',
        'name' => 'Blue Runner by Other Vendor',
        'selling_price' => 450.00,
    ]);
    $wrongVendorProduct->categories()->sync([$category->id]);
    $wrongVendorProduct->colors()->sync([$blue->id]);

    $response = $this->get(route('products.index', [
        'search' => 'Blue',
        'vendor' => 'artisan-vendor',
        'category' => 'runners',
        'colors' => ['blue'],
        'min_price' => 300,
        'max_price' => 600,
    ]));

    $response
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('products/index')
            ->has('products', 1)
            ->where('products.0.id', $matchingProduct->id)
            ->where('search', 'Blue')
            ->where('selected_vendor', 'artisan-vendor')
            ->where('selected_category', 'runners')
            ->where('selected_colors', ['blue'])
            ->where('min_price', '300')
            ->where('max_price', '600')
        );
});

test('pagination links keep filter query parameters', function () {
    $approvedVendor = Vendor::factory()->create([
        'status' => 'approved',
        'slug' => 'heritage-loom',
    ]);

    $category = ProductCategory::factory()->create([
        'name' => 'Throws',
        'slug' => 'throws',
    ]);
    $beige = ProductColor::factory()->create([
        'name' => 'Beige',
        'slug' => 'beige',
    ]);

    Product::factory()->count(12)->create([
        'vendor_id' => $approvedVendor->id,
        'status' => 'active',
        'name' => 'Heritage Throw',
        'selling_price' => 500.00,
    ])->each(function (Product $product) use ($category, $beige): void {
        $product->categories()->sync([$category->id]);
        $product->colors()->sync([$beige->id]);
    });

    $response = $this->get(route('products.index', [
        'search' => 'Heritage',
        'vendor' => 'heritage-loom',
        'category' => 'throws',
        'colors' => ['beige'],
        'min_price' => 400,
        'max_price' => 700,
        'per_page' => 9,
    ]));

    $response->assertSuccessful();

    $pageData = $response->viewData('page');
    $links = collect($pageData['props']['pagination']['links']);
    $nextPageUrl = $links
        ->pluck('url')
        ->filter()
        ->first(fn (string $url): bool => str_contains($url, 'page=2'));

    expect($nextPageUrl)->not->toBeNull();
    expect($nextPageUrl)->toContain('search=Heritage');
    expect($nextPageUrl)->toContain('vendor=heritage-loom');
    expect($nextPageUrl)->toContain('category=throws');
    expect($nextPageUrl)->toContain('min_price=400');
    expect($nextPageUrl)->toContain('max_price=700');
    expect($nextPageUrl)->toContain('per_page=9');
    expect($nextPageUrl)->toContain('colors%5B0%5D=beige');
});
