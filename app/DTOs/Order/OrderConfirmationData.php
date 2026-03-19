<?php

namespace App\DTOs\Order;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;

class OrderConfirmationData
{
    public function __construct(
        public ?User $user,
        public Order $order,
        public ?int $guestOrderId,
    ) {}

    public static function fromRequest(Request $request, Order $order): self
    {
        $guestOrderId = $request->session()->get('guest_order_id');

        return new self(
            $request->user(),
            $order,
            is_numeric($guestOrderId) ? (int) $guestOrderId : null,
        );
    }
}
