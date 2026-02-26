<?php

namespace App\Actions\Product;

use App\DTOs\Product\ProductEditFormData;
use App\DTOs\Product\ProductEditFormResult;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class PrepareProductEditing
{
    public function handle(ProductEditFormData $data): ProductEditFormResult
    {
        Gate::authorize('update', $data->product);

        $product = $data->product->load([
            'media' => fn ($query) => $query
                ->where('type', 'image')
                ->orderBy('sort_order'),
        ]);

        return new ProductEditFormResult(
            '7.00',
            $data->user->vendor?->display_name,
            $data->user->vendor?->slug,
            [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'vendor_price' => (string) $product->vendor_price,
                'materials' => $product->materials,
                'pieces_count' => $product->pieces_count,
                'production_time_days' => $product->production_time_days,
                'dimension_length' => $product->dimension_length !== null ? (float) $product->dimension_length : null,
                'dimension_width' => $product->dimension_width !== null ? (float) $product->dimension_width : null,
                'dimension_height' => $product->dimension_height !== null ? (float) $product->dimension_height : null,
                'dimension_unit' => $product->dimension_unit,
                'images' => $product->media
                    ->map(static fn ($media): array => [
                        'id' => $media->id,
                        'url' => Storage::disk('public')->url($media->path),
                    ])
                    ->all(),
            ],
        );
    }
}
