<?php

namespace App\Http\Requests\Admin;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateOfflineOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        $order = $this->route('order');

        if (! $order instanceof Order) {
            return false;
        }

        $payment = $order->payment;

        return $payment !== null
            && in_array($payment->method, ['bank_transfer', 'cod'], true)
            && Gate::allows('manageOffline', $order);
    }

    public function rules(): array
    {
        return [
            'payment_status' => ['required', Rule::in(['paid', 'failed'])],
            'order_status' => ['required', Rule::in(['pending', 'paid', 'confirmed', 'delivered', 'cancelled'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'payment_status.required' => 'Select a payment status.',
            'payment_status.in' => 'Select a valid payment status.',
            'order_status.required' => 'Select an order status.',
            'order_status.in' => 'Select a valid order status.',
        ];
    }
}
