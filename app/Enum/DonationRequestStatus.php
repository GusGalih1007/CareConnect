<?php

namespace App\Enum;

enum DonationRequestStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Rejected = 'rejected';
    case Fulfilled = 'fulfilled';
}
