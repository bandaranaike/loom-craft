<?php

namespace App\DTOs\Admin;

class YouTubeConnectResult
{
    public function __construct(public string $authUrl) {}

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'auth_url' => $this->authUrl,
        ];
    }
}
