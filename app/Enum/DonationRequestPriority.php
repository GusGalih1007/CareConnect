<?php

namespace App\Enum;

enum DonationRequestPriority: string
{
    case Low = 'low';
    case Normal = 'normal';
    case Urgent = "urgent";
}
