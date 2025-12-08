<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    use SoftDeletes;
    protected $table = 'locations';
    protected $primaryKey = 'location_id';
    protected $fillable = [
        'user_id',
        'address',
        'latitude',
        'longitude'
    ];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i',
        'updated_at' => 'datetime:Y-m-d H:i',
        'deleted_at' => 'datetime:Y-m-d H:i',
    ];

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id', 'user_id');
    }

    public function donationRequest()
    {
        return $this->hasMany(DonationRequest::class, 'location_id', 'location_id');
    }
}
