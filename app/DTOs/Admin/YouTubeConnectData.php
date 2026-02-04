<?php

namespace App\DTOs\Admin;

use App\Models\User;

class YouTubeConnectData
{
    public function __construct(public User $user) {}

    public static function fromUser(User $user): self
    {
        return new self($user);
    }
}
