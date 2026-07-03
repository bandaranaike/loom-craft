<?php

use App\Models\Product;
use App\Models\ProductMedia;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('serves robots and sitemap for search engines', function () {
    [$vendorUser, $vendor] = createApprovedVendor();
    $product = Product::factory()->for($vendor)->create([
        'status' => 'active',
        'selling_price' => '180.00',
    ]);
    ProductMedia::factory()->for($product)->create([
        'type' => 'image',
        'path' => 'products/sitemap/product-image.jpg',
        'alt_text' => 'Product image for sitemap',
        'sort_order' => 0,
    ]);
    $vendor->update([
        'logo_path' => 'vendors/logos/sitemap-logo.png',
        'cover_image_path' => 'vendors/covers/sitemap-cover.png',
    ]);

    $this->get(route('robots.txt'))
        ->assertSuccessful()
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
        ->assertSee(route('sitemap.xml'), false)
        ->assertSee('Disallow: /checkout', false)
        ->assertSee('Disallow: /orders/', false);

    $this->get(route('sitemap.xml'))
        ->assertSuccessful()
        ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
        ->assertSee('xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"', false)
        ->assertSee(route('home'), false)
        ->assertSee(route('products.show', $product), false)
        ->assertSee(route('vendors.show', $vendor), false)
        ->assertSee(Storage::disk('public')->url('products/sitemap/product-image.jpg'), false)
        ->assertSee(Storage::disk('public')->url('vendors/logos/sitemap-logo.png'), false)
        ->assertSee(Storage::disk('public')->url('vendors/covers/sitemap-cover.png'), false);
});

it('includes the google analytics tag on public pages when configured', function () {
    config(['services.google.analytics_id' => 'G-TEST123']);

    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('googletagmanager.com/gtag/js?id=G-TEST123', false)
        ->assertSee("gtag('config', 'G-TEST123')", false);
});

it('marks private app pages as noindex', function () {
    $user = User::factory()->create([
        'role' => 'customer',
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('noindex', false)
        ->assertSee('nofollow', false);
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
