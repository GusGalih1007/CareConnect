<?php

namespace App\Models;

use App\Enum\DonationMatchStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DonationItemMatch extends Model
{
    use HasUuids;
    use SoftDeletes;

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
        'matched_quantity' => 'integer',
        'score' => 'integer',
        'donation_item_id' => 'string',
        'donation_request_item_id' => 'string',
        'status' => DonationMatchStatus::class,
    ];

    public function donationItem()
    {
        return $this->belongsTo(DonationItems::class, 'donation_item_id', 'donation_item_id');
    }

    public function requestItem()
    {
        return $this->belongsTo(DonationRequestItems::class, 'donation_request_item_id', 'donation_request_item_id');
    }

    // Helper methods
    public function isPending()
    {
        return $this->status == DonationMatchStatus::Pending;
    }

    public function isAccepted()
    {
        return $this->status == DonationMatchStatus::Accepted;
    }

    public function isRejected()
    {
        return $this->status == DonationMatchStatus::Rejected;
    }
}
