<?php

namespace App\Http\Requests\Admin;

use App\Models\Order;
use App\Models\Shipment;
use App\Models\ShippingService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Validator;

class UpdateShipmentTrackingRequest extends FormRequest
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
            'shipping_carrier_id' => ['required', 'integer', 'exists:shipping_carriers,id'],
            'shipping_service_id' => ['nullable', 'integer', 'exists:shipping_services,id'],
            'tracking_number' => ['required', 'string', 'max:120'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $serviceId = $this->integer('shipping_service_id');

                if ($serviceId === 0) {
                    return;
                }

                $serviceBelongsToCarrier = ShippingService::query()
                    ->whereKey($serviceId)
                    ->where('shipping_carrier_id', $this->integer('shipping_carrier_id'))
                    ->exists();

                if (! $serviceBelongsToCarrier) {
                    $validator->errors()->add('shipping_service_id', 'Select a service level for the selected carrier.');
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
            'shipping_carrier_id.required' => 'Select a courier carrier.',
            'shipping_carrier_id.exists' => 'Select a valid courier carrier.',
            'shipping_service_id.exists' => 'Select a valid carrier service level.',
            'tracking_number.required' => 'Enter the courier tracking number.',
        ];
    }
}
