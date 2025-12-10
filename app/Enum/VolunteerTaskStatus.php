<?php

namespace App\Enum;

enum VolunteerTaskStatus: string
{
    case Offered = 'offered';
    case Accepted = 'accepted';
    case PickingUp = 'picking_up';
    case InTransit = 'in_transit';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
}
