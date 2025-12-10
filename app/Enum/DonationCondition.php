<?php

namespace App\Enum;

enum DonationCondition: string
{
    case New = 'new';
    case GoodUse = 'good_use';
    case NeedRepair = 'needs_repair';
}
