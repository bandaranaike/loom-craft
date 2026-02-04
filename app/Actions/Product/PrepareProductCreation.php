<?php

namespace App\Actions\Product;

use App\DTOs\Product\ProductCreateFormData;
use App\DTOs\Product\ProductCreateFormResult;
use App\Models\Product;
use Illuminate\Support\Facades\Gate;

class PrepareProductCreation
{
    public function handle(ProductCreateFormData $data): ProductCreateFormResult
    {
        Gate::authorize('create', Product::class);

        $vendorName = $data->user->vendor?->display_name;

        return new ProductCreateFormResult('7.00', $vendorName);
    }
}
