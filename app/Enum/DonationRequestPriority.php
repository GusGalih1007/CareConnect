<?php

namespace App\Enum;

enum DonationRequestPriority: string
{
    case Low = 'low';
    case Normal = 'normal';
    case Urgent = "urgent";

    public function getValue(): string
    {
        return $this->value;
    }

    public function getPriorityScore(): int
    {
        return match ($this) {
            self::Low => 0,
            self::Normal => 5,
            self::Urgent => 10,
        };
    }
}
