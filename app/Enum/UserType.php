<?php

namespace App\Enum;

enum UserType: string
{
    case Donor = 'donor';
    case Volunteer = 'volunteer';
    case Receiver = 'receiver';
}
