<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Badge extends Model
{
    use SoftDeletes;
    use HasUuids;

    protected $table = 'badges';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primayrKey = 'badge_id';

    protected $fillable = [
        'badge_name',
        'criteria',
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
}
