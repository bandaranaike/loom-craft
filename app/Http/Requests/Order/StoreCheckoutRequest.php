<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCheckoutRequest extends FormRequest
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
        $requiresGuest = $this->user() === null;

        return [
            'guest_name' => [Rule::requiredIf($requiresGuest), 'string', 'max:150'],
            'guest_email' => [Rule::requiredIf($requiresGuest), 'email', 'max:255'],
            'currency' => ['required', Rule::in(['LKR'])],
            'shipping_responsibility' => ['required', Rule::in(['platform'])],
            'payment_method' => ['required', Rule::in(['paypal', 'paypal_card', 'stripe', 'bank_transfer', 'cod'])],
            'paypal_conversion_confirmed' => [
                Rule::excludeIf(! in_array($this->input('payment_method'), ['paypal', 'paypal_card'], true)),
                'accepted',
            ],

            'shipping_full_name' => ['required', 'string', 'max:150'],
            'shipping_line1' => ['required', 'string', 'max:255'],
            'shipping_line2' => ['nullable', 'string', 'max:255'],
            'shipping_city' => ['required', 'string', 'max:120'],
            'shipping_region' => ['nullable', 'string', 'max:120'],
            'shipping_postal_code' => ['nullable', 'string', 'max:30'],
            'shipping_country_code' => ['required', 'string', 'size:2'],
            'shipping_phone' => ['nullable', 'string', 'max:40'],

            'billing_full_name' => ['required', 'string', 'max:150'],
            'billing_line1' => ['required', 'string', 'max:255'],
            'billing_line2' => ['nullable', 'string', 'max:255'],
            'billing_city' => ['required', 'string', 'max:120'],
            'billing_region' => ['nullable', 'string', 'max:120'],
            'billing_postal_code' => ['nullable', 'string', 'max:30'],
            'billing_country_code' => ['required', 'string', 'size:2'],
            'billing_phone' => ['nullable', 'string', 'max:40'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'guest_name.required' => 'Please enter your full name.',
            'guest_email.required' => 'Please enter a valid email address.',
            'currency.required' => 'Select a currency for this order.',
            'currency.in' => 'Orders are processed in LKR.',
            'shipping_responsibility.required' => 'Shipping responsibility is required.',
            'shipping_responsibility.in' => 'Shipping is handled by the platform for these orders.',
            'payment_method.required' => 'Select a payment method.',
            'payment_method.in' => 'Select a valid payment method.',
            'paypal_conversion_confirmed.accepted' => 'Confirm the LKR to USD conversion before continuing to PayPal.',
            'shipping_full_name.required' => 'Enter the shipping recipient name.',
            'shipping_line1.required' => 'Enter the shipping street address.',
            'shipping_city.required' => 'Enter the shipping city.',
            'shipping_country_code.required' => 'Enter the shipping country code.',
            'billing_full_name.required' => 'Enter the billing name.',
            'billing_line1.required' => 'Enter the billing street address.',
            'billing_city.required' => 'Enter the billing city.',
            'billing_country_code.required' => 'Enter the billing country code.',
        ];
    }
}
