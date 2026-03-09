<?php

namespace App\Actions\Product;

use App\DTOs\Product\ProductUpdateData;
use Illuminate\Support\Facades\Gate;

class UpdateProduct
{
    public function handle(ProductUpdateData $data): void
    {
        Gate::authorize('update', $data->product);

        $sellingPrice = $data->vendorPrice->addPercentage('7.00');

        $data->product->forceFill([
            'name' => $data->name,
            'description' => $data->description,
            'vendor_price' => $data->vendorPrice->amount,
            'commission_rate' => '7.00',
            'selling_price' => $sellingPrice->amount,
            'materials' => $data->materials,
            'pieces_count' => $data->piecesCount,
            'production_time_days' => $data->productionTimeDays,
            ...$data->dimensions->toArray(),
        ])->save();

        $data->product->categories()->sync($data->categoryIds);
    }
}
