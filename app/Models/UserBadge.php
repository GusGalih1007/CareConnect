<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserBadge extends Model
{
    use SoftDeletes;
    use HasUuids;

    protected $table = 'user_badges';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'user_badge_id';
    protected $fillable = [
        'user_id',
        'badge_id',
        'awarded_at'
    ];

    protected $casts = [
        'user_id' => 'string',
        'badge_id' => 'string',
        'awarded_at' => 'datetime:Y-m-d H:i',
        'created_at' => 'datetime:Y-m-d H:i',
        'updated_at' => 'datetime:Y-m-d H:i',
        'deleted_at' => 'datetime:Y-m-d H:i',
    ];

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id', 'user_id');
    }
    public function badge()
    {
        return $this->belongsTo(Badge::class, 'badge_id', 'badge_id');
    }
}
