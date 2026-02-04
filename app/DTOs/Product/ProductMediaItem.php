<?php

namespace App\DTOs\Product;

class ProductMediaItem
{
    public function __construct(
        public string $type,
        public string $url,
        public ?string $altText,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'url' => $this->url,
            'alt_text' => $this->altText,
        ];
    }
}
