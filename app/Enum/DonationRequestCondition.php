<?php

namespace App\Enum;

enum DonationRequestCondition: string
{
    case New = 'new';
    case GoodUse = 'good_use';
    case NeedRepair = 'needs_repair';
}
