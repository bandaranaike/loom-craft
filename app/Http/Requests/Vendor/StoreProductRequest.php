<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_code' => ['required', 'string', 'max:100', 'unique:products,product_code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],
            'vendor_price' => ['required', 'numeric', 'min:0.01'],
            'discount_percentage' => ['nullable', 'numeric', 'between:0,100'],
            'materials' => ['nullable', 'string', 'max:2000'],
            'pieces_count' => ['nullable', 'integer', 'min:1'],
            'production_time_days' => ['nullable', 'integer', 'min:1'],
            'dimension_length' => ['nullable', 'numeric', 'min:0.01'],
            'dimension_width' => ['nullable', 'numeric', 'min:0.01'],
            'dimension_height' => ['nullable', 'numeric', 'min:0.01'],
            'dimension_unit' => ['nullable', 'string', 'max:20'],
            'category_ids' => ['required', 'array', 'min:1'],
            'category_ids.*' => [
                'integer',
                Rule::exists('product_categories', 'id')->where('is_active', true),
            ],
            'color_ids' => ['required', 'array', 'min:1'],
            'color_ids.*' => [
                'integer',
                Rule::exists('product_colors', 'id')->where('is_active', true),
            ],
            'images' => ['required', 'array', 'min:1'],
            'images.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:10240'],
            'video' => [
                'nullable',
                'file',
                'mimetypes:video/mp4,video/quicktime,video/webm,video/x-matroska',
                'max:256000',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'product_code.required' => 'Please provide a product code.',
            'product_code.unique' => 'This product code is already in use.',
            'name.required' => 'Please provide a product name.',
            'description.required' => 'Please provide a product description.',
            'vendor_price.required' => 'Please provide a vendor price.',
            'vendor_price.min' => 'Vendor price must be at least 0.01.',
            'discount_percentage.between' => 'Discount percentage must be between 0 and 100.',
            'category_ids.required' => 'Please select at least one category.',
            'category_ids.min' => 'Please select at least one category.',
            'category_ids.*.exists' => 'Selected category is invalid or inactive.',
            'color_ids.required' => 'Please select at least one color.',
            'color_ids.min' => 'Please select at least one color.',
            'color_ids.*.exists' => 'Selected color is invalid or inactive.',
            'images.required' => 'Please add at least one product image.',
            'images.min' => 'Please add at least one product image.',
            'images.*.required' => 'Each image file is required.',
            'images.*.image' => 'Images must be valid image files.',
            'images.*.mimes' => 'Images must be jpg, jpeg, png, webp, or gif.',
            'video.mimetypes' => 'Video must be mp4, mov, webm, or mkv.',
        ];
    }
}
