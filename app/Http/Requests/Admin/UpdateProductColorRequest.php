<?php

namespace App\Http\Requests\Admin;

use App\Models\ProductColor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductColorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var ProductColor $productColor */
        $productColor = $this->route('productColor');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('product_colors', 'name')->ignore($productColor->id),
            ],
            'slug' => [
                'nullable',
                'string',
                'max:120',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            ],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Color name is required.',
            'name.unique' => 'This color name already exists.',
            'slug.regex' => 'Slug may only contain lowercase letters, numbers, and hyphens.',
        ];
    }
}
