<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminAction extends Model
{
    use SoftDeletes;
    use HasUuids;

    protected $table = 'admin_actions';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'admin_action_id';

    protected $fillable = [
        'admin_id',
        'action',
        'target_type',
        'target_id',
        'meta'
    ];

    protected $casts = [
        'admin_id' => 'string',
        'meta' => 'json',
        'created_at' => 'datetime:Y-m-d H:i',
        'updated_at' => 'datetime:Y-m-d H:i',
        'deleted_at' => 'datetime:Y-m-d H:i',
    ];
}
