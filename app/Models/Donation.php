<?php

namespace App\Models;

use App\Enum\DonationCondition;
use App\Enum\DonationStatus;
use App\Enum\DonationType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Donation extends Model
{
    use HasUuids;
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

    public function targetRequest()
    {
        return $this->belongsTo(DonationRequest::class, 'request_id', 'donation_request_id');
    }

    public function matches()
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

    public function items()
    {
        return $this->hasMany(DonationItems::class, 'donation_id', 'donation_id');
    }

    public function itemMatches()
    {
        return $this->hasManyThrough(
            DonationItemMatch::class,
            DonationItems::class,
            'donation_id', // Foreign key on donation_items table
            'donation_item_id', // Foreign key on donation_item_matches table
            'donation_id', // Local key on donations table
            'donation_item_id' // Local key on donation_items table
        );
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('status', DonationStatus::Available);
    }

    public function scopeForRequest($query, $requestId)
    {
        return $query->where('request_id', $requestId);
    }

    // Helper methods
    public function isAvailable()
    {
        return $this->status == DonationStatus::Available;
    }

    public function isReserved()
    {
        return $this->status == DonationStatus::Reserved;
    }

    public function getTotalItemsAttribute()
    {
        return $this->items->sum('quantity');
    }

    public function getAvailableItemsAttribute()
    {
        return $this->items->where('status', DonationStatus::Available)->sum('quantity');
    }
}
