<?php

namespace App\Enums;

enum OrderReturnStatus: string
{
    case Requested = 'requested';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case InTransit = 'in_transit';
    case ReceivedByAdmin = 'received_by_admin';
    case Inspected = 'inspected';
    case VendorReview = 'vendor_review';
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
