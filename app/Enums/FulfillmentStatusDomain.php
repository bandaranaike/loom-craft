<?php

namespace App\Enums;

enum FulfillmentStatusDomain: string
{
    case Order = 'order';
    case Payment = 'payment';
    case Shipment = 'shipment';
}
