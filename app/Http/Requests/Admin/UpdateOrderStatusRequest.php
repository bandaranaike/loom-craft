<?php

namespace App\Http\Requests\Admin;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\Fulfillment\FulfillmentStatusService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
            'order_status' => ['required', Rule::in(OrderStatus::values())],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $order = $this->route('order');

                if (! $order instanceof Order || $validator->errors()->isNotEmpty()) {
                    return;
                }

                if (! app(FulfillmentStatusService::class)->canTransitionOrder(
                    $order,
                    $this->validated('order_status'),
                    $this->user(),
                )) {
                    $validator->errors()->add('order_status', 'Select a valid next order status.');
                }
            },
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
