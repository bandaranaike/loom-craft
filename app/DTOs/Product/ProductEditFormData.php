<?php

namespace App\DTOs\Product;

use App\Models\Product;
use App\Models\User;

class ProductEditFormData
{
    public function __construct(
        public User $user,
        public Product $product,
    ) {}

    public static function fromModel(User $user, Product $product): self
    {
        return new self($user, $product);
    }
}
