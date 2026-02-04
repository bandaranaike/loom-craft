<?php

namespace App\DTOs\Admin;

use App\Http\Requests\Admin\YouTubeOAuthCallbackRequest;
use App\Models\User;

class YouTubeCallbackData
{
    public function __construct(
        public User $user,
        public string $code,
        public string $state,
        public ?string $sessionState,
    ) {}

    public static function fromRequest(YouTubeOAuthCallbackRequest $request): self
    {
        return new self(
            $request->user(),
            $request->string('code')->toString(),
            $request->string('state')->toString(),
            $request->session()->get('youtube_oauth_state'),
        );
    }
}
