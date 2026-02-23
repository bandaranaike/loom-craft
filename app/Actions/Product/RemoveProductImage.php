<?php

namespace App\Actions\Product;

use App\Models\Product;
use App\Models\ProductMedia;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RemoveProductImage
{
    public function handle(Product $product, ProductMedia $image): void
    {
        Gate::authorize('update', $product);

        if ($image->product_id !== $product->id || $image->type !== 'image') {
            throw new NotFoundHttpException;
        }

        Storage::disk('public')->delete($image->path);
        $image->delete();
    }
}
