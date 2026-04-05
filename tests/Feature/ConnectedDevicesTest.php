<?php

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

it('redirects guests away from the connected devices page', function () {
    $this->get(route('connected-devices.index'))
        ->assertRedirect(route('login'));
});

it('shows current user tokens on the connected devices page', function () {
    $vendorUser = User::factory()->create(['role' => 'vendor']);

    Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
        'approved_at' => now(),
    ]);

    $firstToken = $vendorUser->createToken('Pixel 10', ['orders:read']);
    $secondToken = $vendorUser->createToken('iPhone 18', ['orders:read', 'orders:update']);

    $vendorUser->tokens()->whereKey($firstToken->accessToken->id)->update([
        'last_used_at' => Carbon::parse('2026-04-06 09:15:00'),
    ]);

    $this->actingAs($vendorUser)
        ->get(route('connected-devices.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('connected-devices/index')
            ->has('tokens', 2)
            ->has('tokens', fn (Assert $tokens) => $tokens
                ->where('0.id', $firstToken->accessToken->id)
                ->where('0.name', 'Pixel 10')
                ->where('0.last_used_at', '2026-04-06T09:15:00+00:00')
                ->where('1.id', $secondToken->accessToken->id)
                ->where('1.name', 'iPhone 18')
                ->etc()
            )
        );
});

it('forbids customers from viewing connected devices', function () {
    $customer = User::factory()->create(['role' => 'customer']);

    $this->actingAs($customer)
        ->get(route('connected-devices.index'))
        ->assertForbidden();
});

it('allows users to revoke their own connected device token', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $token = $admin->createToken('Pixel 10', ['orders:read']);

    $this->actingAs($admin)
        ->delete(route('connected-devices.destroy', $token->accessToken->id))
        ->assertRedirect(route('connected-devices.index'))
        ->assertSessionHas('status', 'Connected device revoked.');

    $this->assertDatabaseMissing('personal_access_tokens', [
        'id' => $token->accessToken->id,
    ]);
});

it('does not allow users to revoke another users token', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $vendorUser = User::factory()->create(['role' => 'vendor']);

    Vendor::factory()->for($vendorUser)->create([
        'status' => 'approved',
        'approved_at' => now(),
    ]);

    $token = $vendorUser->createToken('Pixel 10', ['orders:read']);

    $this->actingAs($admin)
        ->delete(route('connected-devices.destroy', $token->accessToken->id))
        ->assertNotFound();

    $this->assertDatabaseHas('personal_access_tokens', [
        'id' => $token->accessToken->id,
    ]);
});
