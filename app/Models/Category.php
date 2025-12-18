<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Category extends Model
{
    use SoftDeletes;
    use HasUuids;
    protected $table = 'categories';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'category_id';

    protected $fillable = [
        'category_name',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i',
        'updated_at' => 'datetime:Y-m-d H:i',
        'deleted_at' => 'datetime:Y-m-d H:i',
    ];

    // public function donationRequest()
    // {
    //     return $this->hasMany(DonationRequest::class, 'category_id', 'category_id');
    // }

    // public function donation()
    // {
    //     return $this->hasMany(Donation::class, 'category_id', 'category_id');
    // }
    public function requestItem()
    {
        return $this->hasMany(DonationRequestItems::class, 'category_id', 'category_id');
    }

    public function donationItem()
    {
        return $this->hasMany(DonationItems::class, 'category_id', 'category_id');
    }
}
