<?php

namespace App\Enums;

enum ComplaintStatus: string
{
    case Open = 'open';
    case InReview = 'in_review';
    case WaitingForCustomer = 'waiting_for_customer';
    case WaitingForVendor = 'waiting_for_vendor';
    case WaitingForCourier = 'waiting_for_courier';
    case Resolved = 'resolved';
    case Closed = 'closed';
    case Cancelled = 'cancelled';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $status): string => $status->value,
            self::cases(),
        );
    }
}
