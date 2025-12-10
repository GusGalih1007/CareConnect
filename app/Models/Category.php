<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Category extends Model
{
    use SoftDeletes;

    protected $table = 'categories';

    protected $primaryKey = 'category_id';

    protected $fillable = [
        'category_code',
        'category_name',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i',
        'updated_at' => 'datetime:Y-m-d H:i',
        'deleted_at' => 'datetime:Y-m-d H:i',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->category_code)) {
                $model->category_code = (string) Str::uuid();
            }
        });
    }

    public function donationRequest()
    {
        return $this->hasMany(DonationRequest::class, 'category_id', 'category_id');
    }

    public function donation()
    {
        return $this->hasMany(Donation::class, 'category_id', 'category_id');
    }
}
