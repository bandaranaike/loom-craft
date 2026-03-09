<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexPublicProductsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'in:9,12,24'],
            'category' => [
                'nullable',
                'string',
                Rule::exists('product_categories', 'slug')->where('is_active', true),
            ],
            'colors' => ['nullable', 'array'],
            'colors.*' => [
                'string',
                Rule::exists('product_colors', 'slug')->where('is_active', true),
            ],
        ];
    }
}
