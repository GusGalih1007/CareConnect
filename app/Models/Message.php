<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Message extends Model
{
    use SoftDeletes;
    use HasUuids;

    protected $table = 'messages';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'message_id';
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'body',
        'attachments',
        'is_read'
    ];

    protected $casts = [
        'sender_id' => 'string',
        'receiver_id' => 'string',
        'created_at' => 'datetime:Y-m-d H:i',
        'updated_at' => 'datetime:Y-m-d H:i',
        'deleted_at' => 'datetime:Y-m-d H:i',
        'attachments' => 'json',
        'is_read' => 'boolean'
    ];
    public function sender()
    {
        return $this->belongsTo(Users::class, 'sender_id', 'user_id');
    }
    public function receiver()
    {
        return $this->belongsTo(Users::class, 'receiver_id', 'user_id');
    }
}
