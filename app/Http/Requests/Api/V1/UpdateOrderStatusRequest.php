<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Order;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $order = $this->route('order');

        return $order instanceof Order && $this->user()?->can('updateStatus', $order) === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in($this->allowedStatuses())],
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

                if ($this->user()?->role === 'vendor'
                    && ! in_array($order->status, ['paid', 'confirmed'], true)
                    && $this->input('status') === 'shipped') {
                    $validator->errors()->add('status', 'Only paid or confirmed orders can be marked as shipped.');
                }
            },
        ];
    }

    /**
     * @return list<string>
     */
    private function allowedStatuses(): array
    {
        if ($this->user()?->role === 'admin') {
            return ['pending', 'paid', 'confirmed', 'shipped', 'delivered', 'cancelled'];
        }

        return ['shipped'];
    }
}
