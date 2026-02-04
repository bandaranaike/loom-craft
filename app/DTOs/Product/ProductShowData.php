<?php

namespace App\DTOs\Product;

use App\Http\Requests\Product\ShowProductRequest;
use App\Models\User;

class ProductShowData
{
    public function __construct(
        public ?User $user,
        public int $productId,
    ) {}

    public static function fromRequest(ShowProductRequest $request): self
    {
        return new self(
            $request->user(),
            $request->integer('product'),
        );
    }
}
