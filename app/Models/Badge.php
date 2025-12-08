<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Badge extends Model
{
    use SoftDeletes;

    protected $table = 'badges';
    protected $primayrKey = 'badge_id';
    protected $fillable = [
        'badge_code',
        'badge_name',
        'criteria'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i',
        'updated_at' => 'datetime:Y-m-d H:i',
        'deleted_at' => 'datetime:Y-m-d H:i',
    ];

    public function userBadge()
    {
        return $this->hasMany(UserBadge::class, 'badge_id', 'badge_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->badge_code))
            {
                $model->badge_code = (string) Str::uuid();
            }
        });
    }
}
