<?php

use App\Models\ShippingCarrier;
use App\Models\ShippingService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

it('allows admins to manage shipping carriers and services', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->post(route('admin.shipping-carriers.store'), [
            'name' => 'Sri Lanka Post Courier',
            'code' => 'SLPOST',
            'is_active' => '1',
            'sort_order' => '10',
        ])
        ->assertRedirect(route('admin.shipping-carriers.index'))
        ->assertSessionHas('status', 'Carrier created successfully.');

    $carrier = ShippingCarrier::query()->where('name', 'Sri Lanka Post Courier')->firstOrFail();

    $this->actingAs($admin)
        ->post(route('admin.shipping-carriers.services.store', ['shippingCarrier' => $carrier->id]), [
            'name' => 'Registered Parcel',
            'code' => 'REG',
            'is_active' => '1',
            'sort_order' => '20',
        ])
        ->assertRedirect(route('admin.shipping-carriers.index'))
        ->assertSessionHas('status', 'Service created successfully.');

    $this->assertDatabaseHas('shipping_carriers', [
        'id' => $carrier->id,
        'code' => 'SLPOST',
        'is_active' => true,
        'sort_order' => 10,
    ]);

    $this->assertDatabaseHas('shipping_services', [
        'shipping_carrier_id' => $carrier->id,
        'name' => 'Registered Parcel',
        'code' => 'REG',
        'is_active' => true,
        'sort_order' => 20,
    ]);
});

it('shows carriers and services on the admin carrier page', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $carrier = ShippingCarrier::factory()->create(['name' => 'Sri Lanka Post Courier']);
    ShippingService::factory()->for($carrier, 'carrier')->create(['name' => 'Registered Parcel']);

    $this->actingAs($admin)
        ->get(route('admin.shipping-carriers.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/shipping-carriers/index')
            ->where('carriers.0.name', 'Sri Lanka Post Courier')
            ->where('carriers.0.services.0.name', 'Registered Parcel')
        );
});

it('prevents non-admins from managing carriers', function () {
    $customer = User::factory()->create(['role' => 'customer']);

    $this->actingAs($customer)
        ->post(route('admin.shipping-carriers.store'), [
            'name' => 'Sri Lanka Post Courier',
        ])
        ->assertForbidden();
});
