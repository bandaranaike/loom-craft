<?php

namespace App\DTOs\Admin;

class YouTubeCallbackResult
{
    public function __construct(public string $refreshToken) {}
}
