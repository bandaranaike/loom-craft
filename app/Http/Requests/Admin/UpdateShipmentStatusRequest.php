<?php

namespace App\Http\Requests\Admin;

use App\Enums\ShipmentStatus;
use App\Models\Order;
use App\Models\Shipment;
use App\Services\Fulfillment\FulfillmentStatusService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
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

        return $order instanceof Order
            && $shipment instanceof Shipment
            && $shipment->order_id === $order->id
            && $this->user()?->role === 'admin'
            && Gate::allows('updateStatus', $order);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'shipment_status' => ['required', Rule::in(ShipmentStatus::values())],
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
                    $this->validated('shipment_status'),
                    $this->user(),
                )) {
                    $validator->errors()->add('shipment_status', 'Select a valid next shipment status.');
                }
            },
        ];
    }
}
