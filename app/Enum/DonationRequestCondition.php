<?php

namespace App\Enum;

enum DonationRequestCondition: string
{
    case New = 'new';
    case GoodUsed = 'good_used';
    case NeedRepair = 'needs_repair';

    public function getValue(): string
    {
        return $this->value;
    }
    
    public function getNumericValue(): int
    {
        return match($this) {
            self::New => 3,
            self::GoodUsed => 2,
            self::NeedRepair => 1,
        };
    }
}
