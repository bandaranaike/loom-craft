<?php

namespace App\Enums;

enum ShipmentStatus: string
{
    case Pending = 'pending';
    case VendorPreparing = 'vendor_preparing';
    case VendorHandedToAdmin = 'vendor_handed_to_admin';
    case AdminReceived = 'admin_received';
    case QualityChecked = 'quality_checked';
    case ReadyForPacking = 'ready_for_packing';
    case Packed = 'packed';
    case ReadyForDispatch = 'ready_for_dispatch';
    case Dispatched = 'dispatched';
    case InTransit = 'in_transit';
    case Delivered = 'delivered';
    case DeliveryFailed = 'delivery_failed';
    case ReturnToSender = 'return_to_sender';
    case Returned = 'returned';

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

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::VendorPreparing => 'Vendor Preparing',
            self::VendorHandedToAdmin => 'Vendor Handed To Admin',
            self::AdminReceived => 'Admin Received',
            self::QualityChecked => 'Quality Checked',
            self::ReadyForPacking => 'Ready For Packing',
            self::Packed => 'Packed',
            self::ReadyForDispatch => 'Ready For Dispatch',
            self::Dispatched => 'Dispatched',
            self::InTransit => 'In Transit',
            self::Delivered => 'Delivered',
            self::DeliveryFailed => 'Delivery Failed',
            self::ReturnToSender => 'Return To Sender',
            self::Returned => 'Returned',
        };
    }
}
