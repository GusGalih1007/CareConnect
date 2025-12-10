<?php

namespace App\Models;

use App\Enum\DonationCondition;
use App\Enum\DonationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Donation extends Model
{
    use SoftDeletes;

    protected $table = 'donations';
    protected $primaryKey = 'donation_id';

    protected $fillable = [
        'donation_code',
        'user_id',
        'request_id',
        'category_id',
        'title',
        'description',
        'quantity',
        'condition',
        'status',
        'location_id'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i',
        'updated_at' => 'datetime:Y-m-d H:i',
        'deleted_at' => 'datetime:Y-m-d H:i',
        'status' => DonationStatus::class,
        'condition' => DonationCondition::class,
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->donation_code))
            {
                $model->donation_code = Str::uuid();
            }
        });
    }

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
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }
    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id', 'location_id');
    }
}
