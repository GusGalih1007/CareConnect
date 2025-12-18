<?php

namespace App\Enum;

enum DonationType: string
{
    case Single = 'single_item';
    case Multi = 'multiple_items';
}
