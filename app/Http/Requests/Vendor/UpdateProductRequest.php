<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],
            'vendor_price' => ['required', 'numeric', 'min:0.01'],
            'materials' => ['nullable', 'string', 'max:2000'],
            'pieces_count' => ['nullable', 'integer', 'min:1'],
            'production_time_days' => ['nullable', 'integer', 'min:1'],
            'dimension_length' => ['nullable', 'numeric', 'min:0.01'],
            'dimension_width' => ['nullable', 'numeric', 'min:0.01'],
            'dimension_height' => ['nullable', 'numeric', 'min:0.01'],
            'dimension_unit' => ['nullable', 'string', 'max:20'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Please provide a product name.',
            'description.required' => 'Please provide a product description.',
            'vendor_price.required' => 'Please provide a vendor price.',
            'vendor_price.min' => 'Vendor price must be at least 0.01.',
        ];
    }
}
