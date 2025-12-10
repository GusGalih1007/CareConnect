<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Message extends Model
{
    use SoftDeletes;

    protected $table = 'messages';
    protected $primaryKey = 'message_id';

    protected $fillable = [
        'message_code',
        'sender_id',
        'receiver_id',
        'body',
        'attachments',
        'is_read'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i',
        'updated_at' => 'datetime:Y-m-d H:i',
        'deleted_at' => 'datetime:Y-m-d H:i',
        'attachments' => 'json',
        'is_read' => 'boolean'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if(empty($model->message_code))
            {
                $model->message_code = Str::uuid();
            }
        });
    }
    public function sender()
    {
        return $this->belongsTo(Users::class, 'sender_id', 'user_id');
    }
    public function receiver()
    {
        return $this->belongsTo(Users::class, 'receiver_id', 'user_id');
    }
}
