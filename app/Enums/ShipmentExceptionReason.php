<?php

namespace App\Enums;

enum ShipmentExceptionReason: string
{
    case DamagedParcel = 'damaged_parcel';
    case LostParcel = 'lost_parcel';
    case CustomerUnreachable = 'customer_unreachable';
    case AddressIssue = 'address_issue';
    case CustomerRefused = 'customer_refused';
    case Other = 'other';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $reason): string => $reason->value,
            self::cases(),
        );
    }
}
