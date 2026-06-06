<?php

namespace App\DTOs\Cart;

use App\ValueObjects\CartPreparationEstimate;

class CartSummaryResult
{
    /**
     * @param  list<CartItemSummary>  $items
     */
    public function __construct(
        public int $cartId,
        public string $currency,
        public array $items,
        public int $itemCount,
        public string $subtotal,
        public CartPreparationEstimate $preparationEstimate,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'cart_id' => $this->cartId,
            'currency' => $this->currency,
            'items' => array_map(
                static fn (CartItemSummary $item): array => $item->toArray(),
                $this->items,
            ),
            'item_count' => $this->itemCount,
            'subtotal' => $this->subtotal,
            'preparation_estimate' => $this->preparationEstimate->toArray(),
        ];
    }
}
