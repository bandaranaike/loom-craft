<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCartItemRequest extends FormRequest
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
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:25'],
            'currency' => ['nullable', 'string', Rule::in(['USD', 'EUR', 'LKR'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'product_id.required' => 'Select a product to add to your cart.',
            'product_id.exists' => 'That product is not available.',
            'quantity.required' => 'Select a quantity.',
            'quantity.min' => 'Quantity must be at least 1.',
            'quantity.max' => 'Quantity exceeds the maximum allowed.',
            'currency.in' => 'Please choose a supported currency.',
        ];
    }
}
