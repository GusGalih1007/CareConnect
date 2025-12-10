<?php

namespace App\Enum;

enum DonationFinancialStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Failed = 'failed';
}
