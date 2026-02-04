<?php

use App\Models\User;
use Google\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()->instance(Client::class, new class extends Client
    {
        public function createAuthUrl($scope = null, array $queryParams = []): string
        {
            return 'https://example.test/oauth';
        }

        /**
         * @return array<string, string>
         */
        public function fetchAccessTokenWithAuthCode(
            $code,
            $codeVerifier = null
        ): array {
            return [
                'access_token' => 'access-token',
                'refresh_token' => 'refresh-token',
            ];
        }
    });
});

it('allows admins to view the youtube connect page', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->get(route('admin.youtube.connect'))
        ->assertSuccessful();
});

it('blocks non-admins from the youtube connect page', function () {
    $user = User::factory()->create(['role' => 'customer']);

    $this->actingAs($user)
        ->get(route('admin.youtube.connect'))
        ->assertForbidden();
});

it('stores the refresh token after a valid callback', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->withSession(['youtube_oauth_state' => 'state-123'])
        ->get(route('admin.youtube.callback', [
            'code' => 'auth-code',
            'state' => 'state-123',
        ]))
        ->assertRedirect(route('admin.youtube.connect'))
        ->assertSessionHas('youtube_refresh_token', 'refresh-token');
});
