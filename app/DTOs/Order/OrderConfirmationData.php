<?php

namespace App\DTOs\Order;

use App\Models\User;
use Illuminate\Http\Request;

class OrderConfirmationData
{
    public function __construct(
        public ?User $user,
        public int $orderId,
        public ?int $guestOrderId,
    ) {}

    public static function fromRequest(Request $request, int $orderId): self
    {
        $guestOrderId = $request->session()->get('guest_order_id');

        return new self(
            $request->user(),
            $orderId,
            is_numeric($guestOrderId) ? (int) $guestOrderId : null,
        );
    }
}
