<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateVendorProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        $vendor = $this->user()?->vendor;

        return $vendor !== null && $this->user()->can('update', $vendor);
    }

    public function rules(): array
    {
        $vendor = $this->user()?->vendor;

        return [
            'display_name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('vendors', 'slug')->ignore($vendor),
            ],
            'bio' => ['nullable', 'string', 'max:5000'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'whatsapp_number' => ['nullable', 'string', 'max:50'],
            'about_title' => ['nullable', 'string', 'max:255'],
            'craft_specialties' => ['nullable', 'array'],
            'craft_specialties.*' => ['string', 'max:100'],
            'years_active' => ['nullable', 'integer', 'min:0', 'max:200'],
            'location' => ['nullable', 'string', 'max:255'],
            'is_contact_public' => ['required', 'boolean'],
            'is_website_public' => ['required', 'boolean'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:10240'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:10240'],
            'locations' => ['nullable', 'array'],
            'locations.*.id' => [
                'nullable',
                'integer',
                Rule::exists('vendor_locations', 'id')->where('vendor_id', $vendor?->id),
            ],
            'locations.*.location_name' => ['required', 'string', 'max:255'],
            'locations.*.address_line_1' => ['required', 'string', 'max:255'],
            'locations.*.address_line_2' => ['nullable', 'string', 'max:255'],
            'locations.*.city' => ['required', 'string', 'max:255'],
            'locations.*.region' => ['nullable', 'string', 'max:255'],
            'locations.*.postal_code' => ['nullable', 'string', 'max:255'],
            'locations.*.country' => ['required', 'string', 'max:255'],
            'locations.*.phone' => ['nullable', 'string', 'max:50'],
            'locations.*.hours' => ['nullable', 'string', 'max:255'],
            'locations.*.map_url' => ['nullable', 'url', 'max:255'],
            'locations.*.is_primary' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $specialties = $this->input('craft_specialties');

        if (is_string($specialties)) {
            $specialties = collect(preg_split('/[\r\n,]+/', $specialties) ?: [])
                ->map(static fn (string $value): string => trim($value))
                ->filter()
                ->unique()
                ->values()
                ->all();
        }

        $locations = collect($this->input('locations', []))
            ->filter(function (mixed $location): bool {
                if (! is_array($location)) {
                    return false;
                }

                return collect($location)
                    ->except(['id', 'is_primary'])
                    ->contains(static fn (mixed $value): bool => is_string($value) && trim($value) !== '');
            })
            ->map(function (array $location): array {
                return [
                    'id' => $location['id'] ?? null,
                    'location_name' => trim((string) ($location['location_name'] ?? '')),
                    'address_line_1' => trim((string) ($location['address_line_1'] ?? '')),
                    'address_line_2' => $this->nullableTrimmedString($location['address_line_2'] ?? null),
                    'city' => trim((string) ($location['city'] ?? '')),
                    'region' => $this->nullableTrimmedString($location['region'] ?? null),
                    'postal_code' => $this->nullableTrimmedString($location['postal_code'] ?? null),
                    'country' => trim((string) ($location['country'] ?? '')),
                    'phone' => $this->nullableTrimmedString($location['phone'] ?? null),
                    'hours' => $this->nullableTrimmedString($location['hours'] ?? null),
                    'map_url' => $this->nullableTrimmedString($location['map_url'] ?? null),
                    'is_primary' => filter_var(
                        $location['is_primary'] ?? false,
                        FILTER_VALIDATE_BOOLEAN,
                    ),
                ];
            })
            ->values()
            ->all();

        $this->merge([
            'craft_specialties' => is_array($specialties) ? $specialties : [],
            'is_contact_public' => $this->boolean('is_contact_public'),
            'is_website_public' => $this->boolean('is_website_public'),
            'locations' => $locations,
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $locations = collect($this->input('locations', []));

            if ($locations->where('is_primary', true)->count() > 1) {
                $validator->errors()->add('locations', 'Only one location can be marked as primary.');
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'display_name.required' => 'Please provide your display name.',
            'slug.required' => 'Please provide a public vendor slug.',
            'slug.unique' => 'This vendor slug is already in use.',
            'website_url.url' => 'Please provide a valid website URL.',
            'contact_email.email' => 'Please provide a valid contact email.',
            'years_active.max' => 'Years active must be 200 or less.',
            'logo.image' => 'The logo must be a valid image file.',
            'cover_image.image' => 'The cover image must be a valid image file.',
            'locations.*.location_name.required' => 'Please provide a location name.',
            'locations.*.address_line_1.required' => 'Please provide the first address line.',
            'locations.*.city.required' => 'Please provide a city.',
            'locations.*.country.required' => 'Please provide a country.',
            'locations.*.map_url.url' => 'Please provide a valid map URL.',
        ];
    }

    private function nullableTrimmedString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}
