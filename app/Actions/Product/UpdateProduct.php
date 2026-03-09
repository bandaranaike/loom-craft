<?php

namespace App\Actions\Product;

use App\DTOs\Product\ProductUpdateData;
use App\Services\ProductPricingService;
use Illuminate\Support\Facades\Gate;

class UpdateProduct
{
    public function __construct(private ProductPricingService $productPricingService) {}

    public function handle(ProductUpdateData $data): void
    {
        Gate::authorize('update', $data->product);

        $commissionRate = (string) config('commerce.commission_rate');
        $discountPercentage = $data->discountPercentage !== null
            ? $this->productPricingService->normalizePercentage($data->discountPercentage)
            : null;
        $sellingPrice = $data->vendorPrice->addPercentage($commissionRate);

        $data->product->forceFill([
            'product_code' => $data->productCode,
            'name' => $data->name,
            'description' => $data->description,
            'vendor_price' => $data->vendorPrice->amount,
            'commission_rate' => $commissionRate,
            'selling_price' => $sellingPrice->amount,
            'discount_percentage' => $discountPercentage,
            'materials' => $data->materials,
            'pieces_count' => $data->piecesCount,
            'production_time_days' => $data->productionTimeDays,
            ...$data->dimensions->toArray(),
        ])->save();

        $data->product->categories()->sync($data->categoryIds);
        $data->product->colors()->sync($data->colorIds);
    }
}
