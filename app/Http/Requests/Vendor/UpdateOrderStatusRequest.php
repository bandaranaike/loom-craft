<?php

namespace App\Http\Requests\Vendor;

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
            && Gate::allows('viewVendor', $order)
            && Gate::allows('updateStatus', $order);
    }

    public function rules(): array
    {
        return [
            'order_status' => [
                'required',
                Rule::in(['shipped']),
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $order = $this->route('order');

                    if (! $order instanceof Order) {
                        $fail('Select a valid order.');

                        return;
                    }

                    if (! in_array($order->status, ['paid', 'confirmed'], true)) {
                        $fail('Only paid or confirmed orders can be marked as shipped.');
                    }
                },
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'order_status.required' => 'Select an order status.',
            'order_status.in' => 'Vendors can only mark orders as shipped.',
        ];
    }
}
