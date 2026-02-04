<?php

namespace App\DTOs\Product;

use App\Http\Requests\Vendor\IndexProductRequest;
use App\Models\User;

class ProductIndexData
{
    public function __construct(
        public User $user,
        public ?string $search,
        public int $perPage,
    ) {}

    public static function fromRequest(IndexProductRequest $request): self
    {
        return new self(
            $request->user(),
            $request->string('search')->toString() ?: null,
            $request->integer('per_page') ?: 10,
        );
    }
}
