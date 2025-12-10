<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use SoftDeletes;

    protected $table = 'notifications';
    protected $primaryKey = 'notification_id';

    protected $fillable = [
        'user_id',
        'type',
        'payload',
        'is_read',
    ];

    protected $casts = [
        'payload' => 'json',
        'is_read' => 'boolean',
        'created_at' => 'datetime:Y-m-d H:i',
        'updated_at' => 'datetime:Y-m-d H:i',
        'deleted_at' => 'datetime:Y-m-d H:i',
    ];

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id', 'user_id');
    }
}
