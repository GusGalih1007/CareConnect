<?php

namespace App\Models;

use App\Enum\DonationRequestCondition;
use App\Enum\DonationRequestPriority;
use App\Enum\DonationRequestStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class DonationRequest extends Model
{
    use SoftDeletes;

    protected $table = 'donation_requests';
    protected $primaryKey = 'donation_request_id';

    protected $fillable = [
        'request_code',
        'user_id',
        'category_id',
        'title',
        'description',
        'quantity',
        'condition',
        'location_id',
        'priority',
        'status'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'condition' => DonationRequestCondition::class,
        'status' => DonationRequestStatus::class,
        'priority' => DonationRequestPriority::class,
        'created_at' => 'datetime:Y-m-d H:i',
        'updated_at' => 'datetime:Y-m-d H:i',
        'deleted_at' => 'datetime:Y-m-d H:i',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->request_code))
            {
                $model->request_code = Str::uuid();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id', 'user_id');
    }
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }
    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id', 'location_id');
    }
    public function donationRequestValidation()
    {
        return $this->hasMany(DonationRequestValidation::class, 'donation_request_id', 'donation_request_id');
    }
}
