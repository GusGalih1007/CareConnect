<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DonationMatches extends Model
{
    use SoftDeletes;
    use HasUuids;

    protected $table = 'donation_matches';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'donation_match_id';
    protected $fillable = [
        'donation_id',
        'request_id',
        'score'
    ];

    protected $casts = [
        'donation_id' => 'string',
        'request_id' => 'string',
        'created_at'  => 'datetime:Y-m-d H:i',
        'updated_at'  => 'datetime:Y-m-d H:i',
        'deleted_at'  => 'datetime:Y-m-d H:i',
    ];

    public function donation()
    {
        return $this->belongsTo(Donation::class, 'donation_id', 'donation_id');
    }
    public function donationRequest()
    {
        return $this->belongsTo(DonationRequest::class, 'request_id', 'donation_request_id');
    }
}
