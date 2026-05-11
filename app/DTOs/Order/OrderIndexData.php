<?php

namespace App\DTOs\Order;

use App\Enums\OrderStatus;
use App\Models\User;
use Illuminate\Http\Request;

class OrderIndexData
{
    public function __construct(
        public User $user,
        public ?string $status = null,
    ) {}

    /**
     * @var list<string>
     */
    public const ORDER_STATUSES = [
        OrderStatus::Pending->value,
        OrderStatus::Paid->value,
        OrderStatus::Confirmed->value,
        OrderStatus::Fulfilled->value,
        OrderStatus::Closed->value,
        OrderStatus::Cancelled->value,
    ];

    public static function fromRequest(Request $request): self
    {
        $user = $request->user();

        if (! $user instanceof User) {
            throw new \RuntimeException('User is required for order listings.');
        }

        $status = $request->string('status')->toString();

        return new self(
            $user,
            in_array($status, self::ORDER_STATUSES, true) ? $status : null,
        );
    }
}
