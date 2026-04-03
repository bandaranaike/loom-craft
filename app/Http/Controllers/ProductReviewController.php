<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductReviewRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;

class ProductReviewController extends Controller
{
    public function store(
        StoreProductReviewRequest $request,
        Product $product,
    ): RedirectResponse {
        $validated = $request->validated();

        $product->reviews()->firstOrCreate(
            [
                'user_id' => $request->user()->id,
            ],
            [
                'rating' => (int) $validated['rating'],
                'review' => $validated['review'],
            ],
        );

        return redirect()
            ->route('products.show', ['product' => $product->slug])
            ->with('review_status', 'Your review is now visible on this product.');
    }
}
