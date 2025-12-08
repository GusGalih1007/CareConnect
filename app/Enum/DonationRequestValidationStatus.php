<?php

namespace App\Enum;

enum DonationRequestValidationStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case NeedRevision = 'need_revision';
}
