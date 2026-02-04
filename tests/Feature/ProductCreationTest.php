<?php

use App\Contracts\VideoUploader;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('allows approved vendors to view the product creation page', function () {
    $vendorUser = User::factory()->create(['role' => 'vendor']);
    $admin = User::factory()->create(['role' => 'admin']);

    Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
        'approved_at' => now(),
        'approved_by' => $admin->id,
    ]);

    $this->actingAs($vendorUser)
        ->get(route('vendor.products.create'))
        ->assertSuccessful();
});

it('creates products for approved vendors', function () {
    $vendorUser = User::factory()->create(['role' => 'vendor']);
    $admin = User::factory()->create(['role' => 'admin']);

    Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
        'approved_at' => now(),
        'approved_by' => $admin->id,
    ]);

    Storage::fake('public');

    app()->instance(VideoUploader::class, new class implements VideoUploader
    {
        public function upload(UploadedFile $file, User $user): string
        {
            return 'https://www.youtube.com/watch?v=fake-video-id';
        }
    });

    $payload = [
        'name' => 'Heritage Runner',
        'description' => 'Handwoven in Dumbara Rataa with heirloom dyes.',
        'vendor_price' => '100.00',
        'materials' => 'Cotton, natural dyes',
        'pieces_count' => 4,
        'production_time_days' => 21,
        'dimension_length' => 120.5,
        'dimension_width' => 60.0,
        'dimension_height' => 2.5,
        'dimension_unit' => 'cm',
        'images' => [
            UploadedFile::fake()->image('runner-front.jpg'),
            UploadedFile::fake()->image('runner-detail.jpg'),
        ],
        'video' => UploadedFile::fake()->create('runner-tour.mp4', 1000, 'video/mp4'),
    ];

    $this->actingAs($vendorUser)
        ->post(route('vendor.products.store'), $payload)
        ->assertRedirect(route('vendor.products.create'));

    $this->assertDatabaseHas('products', [
        'name' => 'Heritage Runner',
        'vendor_price' => '100.00',
        'commission_rate' => '7.00',
        'selling_price' => '107.00',
        'status' => 'pending_review',
    ]);

    $product = Product::query()->where('name', 'Heritage Runner')->firstOrFail();

    $this->assertSame(2, $product->media()->where('type', 'image')->count());

    $this->assertDatabaseHas('product_media', [
        'product_id' => $product->id,
        'type' => 'video',
        'path' => 'https://www.youtube.com/watch?v=fake-video-id',
    ]);

    $storedImages = $product->media()->where('type', 'image')->pluck('path')->all();
    foreach ($storedImages as $path) {
        Storage::disk('public')->assertExists($path);
    }
});

it('prevents non-vendors from creating products', function () {
    $customer = User::factory()->create(['role' => 'customer']);

    $this->actingAs($customer)
        ->get(route('vendor.products.create'))
        ->assertForbidden();

    $this->actingAs($customer)
        ->post(route('vendor.products.store'), [
            'name' => 'Blocked',
            'description' => 'Not allowed.',
            'vendor_price' => '50.00',
            'images' => [UploadedFile::fake()->image('blocked.jpg')],
        ])
        ->assertForbidden();
});
