<?php

namespace App\DTOs\Order;

class OrderPlacementResult
{
    public function __construct(
        public int $orderId,
        public string $publicOrderId,
        public ?string $guestToken,
    ) {}
}
