<?php

namespace App\DTOs\Order;

class OrderAddressSummary
{
    public function __construct(
        public string $type,
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
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'full_name' => $this->fullName,
            'line1' => $this->line1,
            'line2' => $this->line2,
            'city' => $this->city,
            'region' => $this->region,
            'postal_code' => $this->postalCode,
            'country_code' => $this->countryCode,
            'phone' => $this->phone,
        ];
    }
}
