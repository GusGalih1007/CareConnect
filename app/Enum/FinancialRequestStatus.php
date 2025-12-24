<?php

namespace App\Enum;

enum FinancialRequestStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Open = 'open';
    case Funded = 'funded';
    case Disbursed = 'disbursed';
    case Closed = 'closed';

    public function getValue(): string
    {
        return $this->value;
    }
}
