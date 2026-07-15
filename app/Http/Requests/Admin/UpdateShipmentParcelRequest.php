<?php

namespace App\Http\Requests\Admin;

use App\Models\Order;
use App\Models\Shipment;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateShipmentParcelRequest extends FormRequest
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
            'package_count' => ['required', 'integer', 'min:1', 'max:999'],
            'parcel_item_count' => ['required', 'integer', 'min:0', 'max:999999'],
            'parcel_weight' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'weight_unit' => ['nullable', 'string', 'max:10'],
            'parcel_length' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'parcel_width' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'parcel_height' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'parcel_dimension_unit' => ['nullable', 'string', 'max:10'],
            'parcel_styles' => ['nullable', 'string', 'max:2000'],
            'parcel_materials' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
