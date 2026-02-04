<?php

namespace App\DTOs\Product;

class ProductPublicIndexResult
{
    /**
     * @param  list<ProductPublicListItem>  $products
     * @param  array{
     *     total: int,
     *     per_page: int,
     *     current_page: int,
     *     last_page: int,
     *     from: int|null,
     *     to: int|null,
     *     links: list<array{url: string|null, label: string, active: bool}>
     * }  $pagination
     */
    public function __construct(
        public array $products,
        public array $pagination,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'products' => array_map(
                static fn (ProductPublicListItem $item): array => $item->toArray(),
                $this->products,
            ),
            'pagination' => $this->pagination,
        ];
    }
}
