<?php

namespace App\Enum;

enum DonationMatchStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Fulfilled = 'fulfilled';
}
