<?php

namespace App\Http\Requests\Admin;

use App\Models\Order;
use App\Models\OrderReturn;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateOrderReturnTrackingRequest extends FormRequest
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
            'shipping_carrier_id' => ['required', 'integer', Rule::exists('shipping_carriers', 'id')],
            'shipping_service_id' => ['nullable', 'integer', Rule::exists('shipping_services', 'id')],
            'tracking_number' => ['required', 'string', 'max:255'],
        ];
    }
}
