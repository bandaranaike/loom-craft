<?php

namespace App\DTOs\Product;

class ProductDimensions
{
    public function __construct(
        public ?float $length,
        public ?float $width,
        public ?float $height,
        public ?string $unit,
    ) {}

    /**
     * @return array<string, float|string|null>
     */
    public function toArray(): array
    {
        return [
            'length' => $this->length,
            'width' => $this->width,
            'height' => $this->height,
            'unit' => $this->unit,
        ];
    }
}
