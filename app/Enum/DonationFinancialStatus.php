<?php

namespace App\Enum;

enum DonationFinancialStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Failed = 'failed';

    public function getValue(): string
    {
        return $this->value;
    }
}
