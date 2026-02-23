<?php

namespace App\Actions\Product;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class AddProductImages
{
    /**
     * @param  list<UploadedFile>  $images
     */
    public function handle(Product $product, array $images): void
    {
        Gate::authorize('update', $product);

        $validImages = array_values(array_filter(
            $images,
            static fn ($file): bool => $file instanceof UploadedFile && $file->isValid()
        ));

        if ($validImages === []) {
            return;
        }

        $nextSortOrder = (int) $product->media()
            ->where('type', 'image')
            ->max('sort_order');

        foreach ($validImages as $index => $image) {
            $path = Storage::disk('public')->putFile('products/images', $image);

            $product->media()->create([
                'type' => 'image',
                'path' => $path,
                'sort_order' => $nextSortOrder + $index + 1,
            ]);
        }
    }
}
