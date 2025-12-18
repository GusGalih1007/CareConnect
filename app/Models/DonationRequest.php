<?php

namespace App\Models;

use App\Enum\DonationRequestCondition;
use App\Enum\DonationRequestPriority;
use App\Enum\DonationRequestStatus;
use App\Enum\DonationType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class DonationRequest extends Model
{
    use SoftDeletes;
    use HasUuids;

    protected $table = 'donation_requests';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $primaryKey = 'donation_request_id';

    protected $fillable = [
        'user_id',
        // 'category_id',
        'title',
        'general_description',
        'donation_type',
        // 'quantity',
        // 'condition',
        'location_id',
        'priority',
        'status',
    ];

    protected $casts = [
        'user_id' => 'string',
        // 'category_id' => 'string',
        'donation_type' => DonationType::class,
        'location_id' => 'string',
        // 'quantity' => 'integer',
        // 'condition' => DonationRequestCondition::class,
        'status' => DonationRequestStatus::class,
        'priority' => DonationRequestPriority::class,
        'created_at' => 'datetime:Y-m-d H:i',
        'updated_at' => 'datetime:Y-m-d H:i',
        'deleted_at' => 'datetime:Y-m-d H:i',
    ];

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id', 'user_id');
    }

    // public function category()
    // {
    //     return $this->belongsTo(Category::class, 'category_id', 'category_id');
    // }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id', 'location_id');
    }

    public function donationRequestValidation()
    {
        return $this->hasMany(DonationRequestValidation::class, 'donation_request_id', 'donation_request_id');
    }

    public function donation()
    {
        return $this->hasMany(Donation::class, 'request_id', 'donation_request_id');
    }

    public function donationMatch()
    {
        return $this->hasMany(DonationMatches::class, 'request_id', 'donation_request_id');
    }

    public function attachment()
    {
        return $this->hasMany(Attachment::class, 'owner_id', 'donation_request_id');
    }

    public function requestItem()
    {
        return $this->hasMany(DonationRequestItems::class, 'donation_request_id', 'donation_request_id');
    }
}
