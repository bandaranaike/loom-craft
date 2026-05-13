<?php

namespace App\Http\Requests\Admin;

use App\Models\ShippingCarrier;
use App\Models\ShippingService;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateShippingServiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $carrier = $this->route('shippingCarrier');
        $service = $this->route('shippingService');

        return $this->user()?->role === 'admin'
            && $carrier instanceof ShippingCarrier
            && $service instanceof ShippingService
            && $service->shipping_carrier_id === $carrier->id
            && Gate::allows('access', User::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $carrier = $this->route('shippingCarrier');
        $service = $this->route('shippingService');
        $carrierId = $carrier instanceof ShippingCarrier ? $carrier->id : null;
        $serviceId = $service instanceof ShippingService ? $service->id : null;

        return [
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('shipping_services', 'name')->where('shipping_carrier_id', $carrierId)->ignore($serviceId),
            ],
            'code' => [
                'nullable',
                'string',
                'max:40',
                Rule::unique('shipping_services', 'code')->where('shipping_carrier_id', $carrierId)->ignore($serviceId),
            ],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
