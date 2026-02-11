<?php

namespace App\DTOs\Order;

class OrderAddressData
{
    public function __construct(
        public string $fullName,
        public string $line1,
        public ?string $line2,
        public string $city,
        public ?string $region,
        public ?string $postalCode,
        public string $countryCode,
        public ?string $phone,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(string $type): array
    {
        return [
            'type' => $type,
            'full_name' => $this->fullName,
            'line1' => $this->line1,
            'line2' => $this->line2,
            'city' => $this->city,
            'region' => $this->region,
            'postal_code' => $this->postalCode,
            'country_code' => strtoupper($this->countryCode),
            'phone' => $this->phone,
        ];
    }
}
