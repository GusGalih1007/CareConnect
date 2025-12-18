<?php

namespace App\Models;

use App\Enum\DonationCondition;
use App\Enum\DonationStatus;
use App\Enum\DonationType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Donation extends Model
{
    use SoftDeletes;

    protected $table = 'donations';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'donation_id';

    protected $fillable = [
        'user_id',
        'request_id',
        // 'category_id',
        'title',
        'general_description',
        // 'quantity',
        // 'condition',
        'donation_type',
        'status',
        'location_id',
    ];

    protected $casts = [
        'user_id' => 'string',
        'request_id' => 'string',
        // 'category_id' => 'string',
        'location_id' => 'string',
        'created_at' => 'datetime:Y-m-d H:i',
        'updated_at' => 'datetime:Y-m-d H:i',
        'deleted_at' => 'datetime:Y-m-d H:i',
        'status' => DonationStatus::class,
        'donation_type' => DonationType::class,
        // 'condition' => DonationCondition::class,
    ];

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id', 'user_id');
    }

    public function donationRequest()
    {
        return $this->belongsTo(DonationRequest::class, 'request_id', 'donation_request_id');
    }

    public function donationMatch()
    {
        return $this->hasMany(DonationMatches::class, 'donation_id', 'donation_id');
    }

    // public function category()
    // {
    //     return $this->belongsTo(Category::class, 'category_id', 'category_id');
    // }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id', 'location_id');
    }
    public function volunteerTask()
    {
        return $this->hasMany(VolunteerTask::class, 'donation_id', 'donation_id');
    }

    public function donationItem()
    {
        return $this->hasMany(DonationItems::class, 'donation_id', 'donation_id');
    }
}
