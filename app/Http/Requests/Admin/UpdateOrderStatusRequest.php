<?php

namespace App\Http\Requests\Admin;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $order = $this->route('order');

        return $order instanceof Order
            && $this->user()?->role === 'admin'
            && Gate::allows('updateStatus', $order);
    }

    public function rules(): array
    {
        return [
            'order_status' => ['required', Rule::in(['pending', 'paid', 'confirmed', 'shipped', 'delivered', 'cancelled'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'order_status.required' => 'Select an order status.',
            'order_status.in' => 'Select a valid order status.',
        ];
    }
}
