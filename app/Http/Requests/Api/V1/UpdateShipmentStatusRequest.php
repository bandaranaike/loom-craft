<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\ShipmentStatus;
use App\Models\Order;
use App\Models\Shipment;
use App\Services\Fulfillment\FulfillmentStatusService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateShipmentStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $order = $this->route('order');
        $shipment = $this->route('shipment');

        if (! $order instanceof Order || ! $shipment instanceof Shipment || $shipment->order_id !== $order->id) {
            return false;
        }

        $user = $this->user();

        if ($user === null || $user->currentAccessToken()?->can('orders:update') !== true) {
            return false;
        }

        if ($user->role === 'admin') {
            return true;
        }

        return $user->role === 'vendor' && $user->can('viewVendor', $order);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(ShipmentStatus::values())],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $order = $this->route('order');
                $shipment = $this->route('shipment');

                if (! $order instanceof Order
                    || ! $shipment instanceof Shipment
                    || $validator->errors()->isNotEmpty()) {
                    return;
                }

                if (! app(FulfillmentStatusService::class)->canTransitionShipment(
                    $order,
                    $shipment,
                    $this->input('status'),
                    $this->user(),
                )) {
                    $validator->errors()->add('status', 'Select a valid next shipment status.');
                }
            },
        ];
    }
}
