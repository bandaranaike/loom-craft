<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;

class StoreFeedbackRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        if ($user === null || $user->role !== 'vendor') {
            return false;
        }

        return $user->vendor !== null && $user->vendor->status === 'approved';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:120'],
            'details' => ['required', 'string', 'max:1500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Please provide a short headline for your feedback.',
            'title.max' => 'The title may not be greater than 120 characters.',
            'details.required' => 'Please provide your feedback details.',
            'details.max' => 'Feedback details may not be greater than 1500 characters.',
        ];
    }
}
