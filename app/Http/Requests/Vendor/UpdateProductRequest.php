<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->has('variations') && $this->filled('vendor_price')) {
            $this->merge([
                'variations' => [
                    [
                        'label' => 'Standard',
                        'vendor_price' => $this->input('vendor_price'),
                        'dimension_length' => $this->input('dimension_length'),
                        'dimension_width' => $this->input('dimension_width'),
                        'dimension_height' => $this->input('dimension_height'),
                    ],
                ],
            ]);
        }
    }

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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('products', 'product_code')->ignore($this->route('product')),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],
            'vendor_price' => ['required', 'numeric', 'min:0.01'],
            'variations' => ['required', 'array', 'min:1'],
            'variations.*.id' => ['nullable', 'integer', Rule::exists('product_variations', 'id')->where('product_id', $this->route('product')?->id)],
            'variations.*.label' => ['required', 'string', 'max:100', 'distinct:strict'],
            'variations.*.vendor_price' => ['required', 'numeric', 'min:0.01'],
            'variations.*.dimension_length' => ['nullable', 'numeric', 'min:0.01'],
            'variations.*.dimension_width' => ['nullable', 'numeric', 'min:0.01'],
            'variations.*.dimension_height' => ['nullable', 'numeric', 'min:0.01'],
            'discount_percentage' => ['nullable', 'numeric', 'between:0,100'],
            'materials' => ['nullable', 'string', 'max:2000'],
            'pieces_count' => ['nullable', 'integer', 'min:1'],
            'production_time_days' => ['nullable', 'integer', 'min:1'],
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
            'variations.required' => 'Please add at least one size and price.',
            'variations.min' => 'Please add at least one size and price.',
            'variations.*.id.exists' => 'Selected variation is invalid for this product.',
            'variations.*.label.required' => 'Each variation needs a size label.',
            'variations.*.label.distinct' => 'Variation size labels must be unique.',
            'variations.*.vendor_price.required' => 'Each variation needs a vendor price.',
            'variations.*.vendor_price.min' => 'Variation prices must be at least 0.01.',
            'variations.*.dimension_length.min' => 'Variation lengths must be at least 0.01.',
            'variations.*.dimension_width.min' => 'Variation widths must be at least 0.01.',
            'variations.*.dimension_height.min' => 'Variation heights must be at least 0.01.',
            'discount_percentage.between' => 'Discount percentage must be between 0 and 100.',
            'category_ids.required' => 'Please select at least one category.',
            'category_ids.min' => 'Please select at least one category.',
            'category_ids.*.exists' => 'Selected category is invalid or inactive.',
            'color_ids.required' => 'Please select at least one color.',
            'color_ids.min' => 'Please select at least one color.',
            'color_ids.*.exists' => 'Selected color is invalid or inactive.',
        ];
    }
}
