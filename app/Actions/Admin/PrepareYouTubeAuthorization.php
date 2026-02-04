<?php

namespace App\Actions\Admin;

use App\DTOs\Admin\YouTubeConnectData;
use App\DTOs\Admin\YouTubeConnectResult;
use App\Models\User;
use Google\Client;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class PrepareYouTubeAuthorization
{
    public function __construct(private Client $client) {}

    public function handle(YouTubeConnectData $data): YouTubeConnectResult
    {
        Gate::authorize('access', User::class);

        $state = Str::random(40);

        session(['youtube_oauth_state' => $state]);

        $this->client->setRedirectUri(route('admin.youtube.callback', [], true));
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
        $this->client->setScopes(['https://www.googleapis.com/auth/youtube.upload']);
        $this->client->setState($state);

        return new YouTubeConnectResult($this->client->createAuthUrl());
    }
}
