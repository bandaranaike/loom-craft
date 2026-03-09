<?php

use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorLocation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

test('vendors can view their full vendor profile edit page', function () {
    $vendor = Vendor::factory()->create([
        'display_name' => 'Woven House',
        'slug' => 'woven-house',
        'craft_specialties' => ['Handloom', 'Cotton'],
    ]);
    VendorLocation::factory()->for($vendor)->create([
        'location_name' => 'Main Atelier',
        'is_primary' => true,
    ]);

    $this->actingAs($vendor->user)
        ->get(route('vendor.profile.edit'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('vendor/profile/edit')
            ->where('vendor.display_name', 'Woven House')
            ->where('vendor.slug', 'woven-house')
            ->where('vendor.craft_specialties.0', 'Handloom')
            ->where('vendor.locations.0.location_name', 'Main Atelier')
        );
});

test('vendors can update their full vendor profile', function () {
    Storage::fake('public');

    $vendor = Vendor::factory()->create([
        'display_name' => 'Woven House',
        'slug' => 'woven-house',
        'status' => 'approved',
    ]);
    $existingLocation = VendorLocation::factory()->for($vendor)->create([
        'location_name' => 'Old Atelier',
        'city' => 'Matale',
        'country' => 'Sri Lanka',
        'is_primary' => true,
    ]);

    $response = $this->actingAs($vendor->user)->patch(route('vendor.profile.update'), [
        'display_name' => 'Heritage Loom House',
        'slug' => 'heritage-loom-house',
        'bio' => 'A family weaving studio based in Kandy.',
        'tagline' => 'Handloom pieces from Kandy',
        'website_url' => 'https://example.com',
        'contact_email' => 'atelier@example.com',
        'contact_phone' => '+94 77 123 4567',
        'whatsapp_number' => '+94 77 987 6543',
        'about_title' => 'Our weaving story',
        'craft_specialties' => 'Handloom, Natural Dyes',
        'years_active' => 12,
        'location' => 'Kandy, Sri Lanka',
        'is_contact_public' => '1',
        'is_website_public' => '0',
        'locations' => [
            [
                'id' => $existingLocation->id,
                'location_name' => 'Main Atelier',
                'address_line_1' => '12 Loom Street',
                'address_line_2' => 'Upper floor',
                'city' => 'Kandy',
                'region' => 'Central Province',
                'postal_code' => '20000',
                'country' => 'Sri Lanka',
                'phone' => '+94 81 222 1111',
                'hours' => 'Mon-Sat 9AM-5PM',
                'map_url' => 'https://maps.google.com/?q=Kandy',
                'is_primary' => '1',
            ],
            [
                'location_name' => 'Colombo Studio',
                'address_line_1' => '25 Craft Avenue',
                'address_line_2' => '',
                'city' => 'Colombo',
                'region' => 'Western Province',
                'postal_code' => '00100',
                'country' => 'Sri Lanka',
                'phone' => '+94 11 222 3333',
                'hours' => 'Daily 10AM-6PM',
                'map_url' => '',
                'is_primary' => '0',
            ],
        ],
        'logo' => UploadedFile::fake()->image('logo.png'),
        'cover_image' => UploadedFile::fake()->image('cover.jpg'),
    ]);

    $response->assertRedirect(route('vendor.profile.edit'));

    $vendor->refresh();

    expect($vendor->display_name)->toBe('Heritage Loom House')
        ->and($vendor->slug)->toBe('heritage-loom-house')
        ->and($vendor->bio)->toBe('A family weaving studio based in Kandy.')
        ->and($vendor->tagline)->toBe('Handloom pieces from Kandy')
        ->and($vendor->website_url)->toBe('https://example.com')
        ->and($vendor->contact_email)->toBe('atelier@example.com')
        ->and($vendor->contact_phone)->toBe('+94 77 123 4567')
        ->and($vendor->whatsapp_number)->toBe('+94 77 987 6543')
        ->and($vendor->about_title)->toBe('Our weaving story')
        ->and($vendor->craft_specialties)->toBe(['Handloom', 'Natural Dyes'])
        ->and($vendor->years_active)->toBe(12)
        ->and($vendor->location)->toBe('Kandy, Sri Lanka')
        ->and($vendor->is_contact_public)->toBeTrue()
        ->and($vendor->is_website_public)->toBeFalse()
        ->and($vendor->logo_path)->not->toBeNull()
        ->and($vendor->cover_image_path)->not->toBeNull();

    expect($vendor->locations()->count())->toBe(2);
    expect($vendor->locations()->where('location_name', 'Main Atelier')->first())
        ->not->toBeNull();
    expect($vendor->locations()->where('location_name', 'Main Atelier')->first()?->is_primary)
        ->toBeTrue();
    expect($vendor->locations()->where('location_name', 'Colombo Studio')->first())
        ->not->toBeNull();

    Storage::disk('public')->assertExists($vendor->logo_path);
    Storage::disk('public')->assertExists($vendor->cover_image_path);
});

test('removed vendor locations are deleted during profile updates', function () {
    $vendor = Vendor::factory()->create();
    $location = VendorLocation::factory()->for($vendor)->create();

    $this->actingAs($vendor->user)->patch(route('vendor.profile.update'), [
        'display_name' => $vendor->display_name,
        'slug' => $vendor->slug,
        'is_contact_public' => '1',
        'is_website_public' => '1',
        'locations' => [],
    ])->assertRedirect(route('vendor.profile.edit'));

    $this->assertDatabaseMissing('vendor_locations', [
        'id' => $location->id,
    ]);
});

test('users without a vendor cannot access vendor profile management', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('vendor.profile.edit'))
        ->assertForbidden();
});
