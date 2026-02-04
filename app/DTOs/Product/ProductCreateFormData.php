<?php

namespace App\DTOs\Product;

use App\Models\User;

class ProductCreateFormData
{
    public function __construct(public User $user) {}

    public static function fromUser(User $user): self
    {
        return new self($user);
    }
}
