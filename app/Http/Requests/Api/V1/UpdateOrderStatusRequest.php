<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\Fulfillment\FulfillmentStatusService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $order = $this->route('order');

        return $order instanceof Order
            && $this->user()?->role === 'admin'
            && $this->user()?->can('updateStatus', $order) === true
            && $this->user()?->currentAccessToken()?->can('orders:update') === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(OrderStatus::values())],
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
                    $this->input('status'),
                    $this->user(),
                )) {
                    $validator->errors()->add('status', 'Select a valid next order status.');
                }
            },
        ];
    }
}
