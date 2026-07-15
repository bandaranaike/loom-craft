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
        $variations = collect($data->variations)
            ->map(fn (array $variation, int $index): array => [
                'id' => $variation['id'],
                'label' => $variation['label'],
                'vendor_price' => $variation['vendor_price']->amount,
                'selling_price' => $variation['vendor_price']->addPercentage($commissionRate)->amount,
                'dimension_length' => $variation['dimension_length'],
                'dimension_width' => $variation['dimension_width'],
                'dimension_height' => $variation['dimension_height'],
                'sort_order' => $index,
            ])
            ->values();
        $defaultVariation = $variations->sortBy(static fn (array $variation): float => (float) $variation['selling_price'])->first();

        if (! is_array($defaultVariation)) {
            throw new \RuntimeException('At least one product variation is required.');
        }

        $data->product->forceFill([
            'product_code' => $data->productCode,
            'name' => $data->name,
            'description' => $data->description,
            'vendor_price' => $defaultVariation['vendor_price'],
            'commission_rate' => $commissionRate,
            'selling_price' => $defaultVariation['selling_price'],
            'discount_percentage' => $discountPercentage,
            'materials' => $data->materials,
            'pieces_count' => $data->piecesCount,
            'production_time_days' => $data->productionTimeDays,
            'expiry_information' => $data->expiryInformation,
            'dimension_unit' => $data->dimensionUnit,
        ])->save();

        $data->product->categories()->sync($data->categoryIds);
        $data->product->colors()->sync($data->colorIds);
        $this->syncVariations($data, $variations->all());
    }

    /**
     * @param  list<array{id: int|null, label: string, vendor_price: string, selling_price: string, dimension_length: float|null, dimension_width: float|null, dimension_height: float|null, sort_order: int}>  $variations
     */
    private function syncVariations(ProductUpdateData $data, array $variations): void
    {
        $retainedIds = [];

        foreach ($variations as $variation) {
            $id = $variation['id'];
            unset($variation['id']);

            if ($id === null) {
                $existing = $data->product->variations()
                    ->where('label', $variation['label'])
                    ->first();

                if ($existing !== null) {
                    $existing->update($variation);
                    $retainedIds[] = $existing->id;

                    continue;
                }

                $created = $data->product->variations()->create($variation);
                $retainedIds[] = $created->id;

                continue;
            }

            $data->product->variations()
                ->whereKey($id)
                ->update($variation);
            $retainedIds[] = $id;
        }

        $data->product->variations()
            ->whereNotIn('id', $retainedIds)
            ->delete();
    }
}
