<?php

namespace App\Actions\Admin;

use App\DTOs\Admin\YouTubeCallbackData;
use App\DTOs\Admin\YouTubeCallbackResult;
use App\Models\User;
use Google\Client;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class HandleYouTubeCallback
{
    public function __construct(private Client $client) {}

    public function handle(YouTubeCallbackData $data): YouTubeCallbackResult
    {
        Gate::authorize('access', User::class);

        if ($data->sessionState === null || ! hash_equals($data->sessionState, $data->state)) {
            throw ValidationException::withMessages([
                'state' => 'Invalid OAuth state. Please try again.',
            ]);
        }

        $this->client->setRedirectUri(route('admin.youtube.callback', [], true));
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
        $this->client->setScopes(['https://www.googleapis.com/auth/youtube.upload']);

        $token = $this->client->fetchAccessTokenWithAuthCode($data->code);

        if (isset($token['error'])) {
            throw ValidationException::withMessages([
                'code' => 'YouTube authorization failed. Please retry.',
            ]);
        }

        $refreshToken = $token['refresh_token'] ?? null;

        if (! is_string($refreshToken) || $refreshToken === '') {
            throw ValidationException::withMessages([
                'code' => 'No refresh token returned. Revoke access and try again.',
            ]);
        }

        session()->forget('youtube_oauth_state');

        return new YouTubeCallbackResult($refreshToken);
    }
}
