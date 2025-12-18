<?php

namespace App\Models;

use App\Enum\DonationMatchStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DonationItemMatch extends Model
{
    use HasUuids;
    // use SoftDeletes;

    protected $table = 'donation_item_matches';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'donation_item_match_id';

    protected $fillable = [
        'donation_item_id',
        'donation_request_item_id',
        'matched_quantity',
        'score',
        'status',
    ];

    protected $casts = [
        'donation_item_id' => 'string',
        'donation_request_item_id' => 'string',
        'status' => DonationMatchStatus::class,
    ];
}
