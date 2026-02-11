<?php

namespace App\DTOs\Order;

use App\Models\User;
use Illuminate\Http\Request;

class OrderShowData
{
    public function __construct(
        public User $user,
        public int $orderId,
    ) {}

    public static function fromRequest(Request $request, int $orderId): self
    {
        $user = $request->user();

        if (! $user instanceof User) {
            throw new \RuntimeException('User is required to view orders.');
        }

        return new self($user, $orderId);
    }
}
