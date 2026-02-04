<?php

namespace App\Actions\Product;

use App\DTOs\Product\ProductCreateData;
use App\DTOs\Product\ProductCreateResult;
use App\Models\Product;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class CreateProduct
{
    public function handle(ProductCreateData $data): ProductCreateResult
    {
        Gate::authorize('create', Product::class);

        $vendor = $data->user->vendor;

        if ($vendor === null) {
            throw new \RuntimeException('Vendor profile is required to create products.');
        }

        $sellingPrice = $data->vendorPrice->addPercentage('7.00');

        $product = Product::query()->create([
            'vendor_id' => $vendor->id,
            'name' => $data->name,
            'description' => $data->description,
            'vendor_price' => $data->vendorPrice->amount,
            'commission_rate' => '7.00',
            'selling_price' => $sellingPrice->amount,
            'materials' => $data->materials,
            'pieces_count' => $data->piecesCount,
            'production_time_days' => $data->productionTimeDays,
            ...$data->dimensions->toArray(),
            'status' => 'pending_review',
        ]);

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
            $videoUrl = app(\App\Contracts\VideoUploader::class)
                ->upload($data->video, $data->user);

            $product->media()->create([
                'type' => 'video',
                'path' => $videoUrl,
                'sort_order' => count($data->images),
            ]);
        }

        return new ProductCreateResult($product->id, $sellingPrice->amount);
    }
}
