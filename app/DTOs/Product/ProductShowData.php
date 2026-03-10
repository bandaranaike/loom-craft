<?php

namespace App\DTOs\Product;

use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class ProductShowData
{
    public function __construct(public ?User $user, public Product $product) {}

    public static function fromModel(Request $request, Product $product): self
    {
        return new self($request->user(), $product);
    }
}
