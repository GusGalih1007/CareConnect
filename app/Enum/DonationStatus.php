<?php

namespace App\Enum;

enum DonationStatus: string
{
    case Available = 'available';
    case Reserved = 'reserved';
    case PickedUp = 'picked_up';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function getValue(): string
    {
        return $this->value;
    }
}
