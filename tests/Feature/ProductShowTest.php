<?php

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductColor;
use App\Models\Vendor;
use Inertia\Testing\AssertableInertia as Assert;

test('guests can view active products', function () {
    $vendor = Vendor::factory()->create([
        'status' => 'approved',
        'display_name' => 'Heritage Loom Atelier',
    ]);
    $product = Product::factory()->create([
        'vendor_id' => $vendor->id,
        'status' => 'active',
        'name' => 'Dumbara Signature Weave',
    ]);
    $category = ProductCategory::factory()->create([
        'name' => 'Cushion Covers',
        'slug' => 'cushion-covers',
    ]);
    $product->categories()->sync([$category->id]);
    $color = ProductColor::factory()->create([
        'name' => 'Brown',
        'slug' => 'brown',
    ]);
    $product->colors()->sync([$color->id]);
    $firstImage = $product->media()->create([
        'type' => 'image',
        'path' => 'products/example-one.jpg',
        'alt_text' => 'Front view',
        'sort_order' => 1,
    ]);
    $secondImage = $product->media()->create([
        'type' => 'image',
        'path' => 'products/example-two.jpg',
        'alt_text' => 'Detail view',
        'sort_order' => 2,
    ]);

    $response = $this->get(route('products.show', $product));

    $response
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('products/show')
            ->where('product.id', $product->id)
            ->where('product.name', $product->name)
            ->where('product.vendor.display_name', $vendor->display_name)
            ->where('product.categories.0.slug', 'cushion-covers')
            ->where('product.colors.0.slug', 'brown')
            ->where('product.images.0.id', $firstImage->id)
            ->where('product.images.0.alt_text', 'Front view')
            ->where('product.images.1.id', $secondImage->id)
        );
});

test('non-active products are not visible to guests', function () {
    $vendor = Vendor::factory()->create([
        'status' => 'approved',
    ]);
    $product = Product::factory()->create([
        'vendor_id' => $vendor->id,
        'status' => 'pending_review',
    ]);

    $response = $this->get(route('products.show', $product));

    $response->assertNotFound();
});

test('product show returns only image media ordered by sort order and id', function () {
    $vendor = Vendor::factory()->create([
        'status' => 'approved',
    ]);
    $product = Product::factory()->create([
        'vendor_id' => $vendor->id,
        'status' => 'active',
    ]);

    $imageB = $product->media()->create([
        'type' => 'image',
        'path' => 'products/image-b.jpg',
        'alt_text' => 'Second by id',
        'sort_order' => 0,
    ]);
    $imageA = $product->media()->create([
        'type' => 'image',
        'path' => 'products/image-a.jpg',
        'alt_text' => 'First by id',
        'sort_order' => 0,
    ]);
    $product->media()->create([
        'type' => 'video',
        'path' => 'products/video.mp4',
        'alt_text' => null,
        'sort_order' => 1,
    ]);

    $response = $this->get(route('products.show', $product));

    $response
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('products/show')
            ->where('product.images', function (mixed $images) use ($imageA, $imageB): bool {
                $resolvedImages = collect($images)->values();

                return $resolvedImages->count() === 2
                    && $resolvedImages[0]['id'] === $imageB->id
                    && $resolvedImages[1]['id'] === $imageA->id
                    && isset($resolvedImages[0]['url'], $resolvedImages[0]['alt_text'], $resolvedImages[0]['type']);
            })
        );
});
