<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $product = $this->route('product');

        return $user !== null
            && $product instanceof Product
            && $product->canBeReviewedBy($user);
    }

    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', Rule::in([1, 2, 3, 4, 5])],
            'review' => ['required', 'string', 'min:12', 'max:1500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'rating.required' => 'Select a star rating before submitting your review.',
            'rating.in' => 'Ratings must be between 1 and 5 stars.',
            'review.required' => 'Share a few words about the product.',
            'review.min' => 'Reviews should be at least 12 characters.',
            'review.max' => 'Reviews may not exceed 1500 characters.',
        ];
    }
}
