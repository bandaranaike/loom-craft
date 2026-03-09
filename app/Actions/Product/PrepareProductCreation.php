<?php

namespace App\Actions\Product;

use App\DTOs\Product\ProductCreateFormData;
use App\DTOs\Product\ProductCreateFormResult;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\Gate;

class PrepareProductCreation
{
    public function handle(ProductCreateFormData $data): ProductCreateFormResult
    {
        Gate::authorize('create', Product::class);

        $vendorName = $data->user->vendor?->display_name;
        $vendorSlug = $data->user->vendor?->slug;
        $categories = ProductCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(static fn (ProductCategory $category): array => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ])
            ->all();

        return new ProductCreateFormResult('7.00', $vendorName, $vendorSlug, $categories);
    }
}
