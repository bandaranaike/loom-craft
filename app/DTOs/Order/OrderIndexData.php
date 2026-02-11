<?php

namespace App\DTOs\Order;

use App\Models\User;
use Illuminate\Http\Request;

class OrderIndexData
{
    public function __construct(public User $user) {}

    public static function fromRequest(Request $request): self
    {
        $user = $request->user();

        if (! $user instanceof User) {
            throw new \RuntimeException('User is required for order listings.');
        }

        return new self($user);
    }
}
