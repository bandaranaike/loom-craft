<?php

namespace App\Enums;

enum ComplaintCategory: string
{
    case DamagedItem = 'damaged_item';
    case WrongItem = 'wrong_item';
    case LateDelivery = 'late_delivery';
    case MissingItem = 'missing_item';
    case PaymentIssue = 'payment_issue';
    case RefundIssue = 'refund_issue';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $category): string => $category->value,
            self::cases(),
        );
    }
}
