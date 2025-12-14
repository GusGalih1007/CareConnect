<?php

namespace App\Models;

use App\Enum\OtpType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class OtpCode extends Model
{
    use HasUuids;

    protected $table = 'otp_codes';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'otp_id';

    protected $fillable = [
        'user_id',
        'code_type',
        'code_hash',
        'attempts',
        'expires_at',
        'used_at'
    ];

    protected $casts = [
        'user_id' => 'string',
        'code_type' => OtpType::class,
        'expires_at' => 'datetime:Y-m-d H:i',
        'used_at' => 'datetime:Y-m-d H:i',
        'created_at' => 'datetime:Y-m-d H:i',
        'updated_at' => 'datetime:Y-m-d H:i',
    ];

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id', 'user_id');
    }

    public function isExpired()
    {
        return now()->greaterThan($this->expires_at);
    }

    public function isUsed()
    {
        return ! is_null($this->used_at);
    }
}
