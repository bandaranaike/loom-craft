<?php

namespace App\Http\Requests\Admin;

use App\Enums\OrderReturnStatus;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Services\Fulfillment\FulfillmentStatusService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateOrderReturnStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $order = $this->route('order');
        $orderReturn = $this->route('orderReturn');

        return $order instanceof Order
            && $orderReturn instanceof OrderReturn
            && $orderReturn->order_id === $order->id
            && $this->user()?->role === 'admin'
            && Gate::allows('updateStatus', $order);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'return_status' => ['required', 'string', Rule::in(OrderReturnStatus::values())],
            'reason' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:5000'],
            'resolution' => ['nullable', 'string', 'max:64'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $order = $this->route('order');
                $orderReturn = $this->route('orderReturn');

                if (! $order instanceof Order
                    || ! $orderReturn instanceof OrderReturn
                    || $validator->errors()->isNotEmpty()) {
                    return;
                }

                if (! app(FulfillmentStatusService::class)->canTransitionReturn(
                    $order,
                    $orderReturn,
                    $this->validated('return_status'),
                    $this->user(),
                )) {
                    $validator->errors()->add('return_status', 'Select a valid next return status.');
                }
            },
        ];
    }
}
