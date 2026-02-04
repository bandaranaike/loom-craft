<?php

namespace App\ValueObjects;

final readonly class Dimensions
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
            'dimension_length' => $this->length,
            'dimension_width' => $this->width,
            'dimension_height' => $this->height,
            'dimension_unit' => $this->unit,
        ];
    }
}
