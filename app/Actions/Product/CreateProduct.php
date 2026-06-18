<?php

namespace App\Actions\Product;

use App\Contracts\VideoUploader;
use App\DTOs\Product\ProductCreateData;
use App\DTOs\Product\ProductCreateResult;
use App\Models\Product;
use App\Services\ProductPricingService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class CreateProduct
{
    public function __construct(private ProductPricingService $productPricingService) {}

    public function handle(ProductCreateData $data): ProductCreateResult
    {
        Gate::authorize('create', Product::class);

        $vendor = $data->user->vendor;
        $commissionRate = (string) config('commerce.commission_rate');
        $discountPercentage = $data->discountPercentage !== null
            ? $this->productPricingService->normalizePercentage($data->discountPercentage)
            : null;

        if ($vendor === null) {
            throw new \RuntimeException('Vendor profile is required to create products.');
        }

        $variations = collect($data->variations)
            ->map(fn (array $variation, int $index): array => [
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

        $product = Product::query()->create([
            'vendor_id' => $vendor->id,
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
            'dimension_unit' => $data->dimensionUnit,
            'status' => 'pending_review',
        ]);
        $product->categories()->sync($data->categoryIds);
        $product->colors()->sync($data->colorIds);
        $product->variations()->createMany($variations->all());

        if ($data->images !== []) {
            $paths = array_map(
                static fn ($image) => Storage::disk('public')->putFile('products/images', $image),
                $data->images,
            );

            $product->media()->createMany(array_map(
                static fn (string $path, int $index): array => [
                    'type' => 'image',
                    'path' => $path,
                    'sort_order' => $index,
                ],
                $paths,
                array_keys($paths),
            ));
        }

        if ($data->video !== null) {
            $videoUrl = app(VideoUploader::class)
                ->upload($data->video, $data->user);

            $product->media()->create([
                'type' => 'video',
                'path' => $videoUrl,
                'sort_order' => count($data->images),
            ]);
        }

        return new ProductCreateResult($product->id, $defaultVariation['selling_price']);
    }
}
