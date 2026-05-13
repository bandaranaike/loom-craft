<?php

namespace App\Http\Requests\Admin;

use App\Models\ShippingCarrier;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateShippingCarrierRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin'
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
        $carrierId = $carrier instanceof ShippingCarrier ? $carrier->id : null;

        return [
            'name' => ['required', 'string', 'max:120', Rule::unique('shipping_carriers', 'name')->ignore($carrierId)],
            'code' => ['nullable', 'string', 'max:40', Rule::unique('shipping_carriers', 'code')->ignore($carrierId)],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
