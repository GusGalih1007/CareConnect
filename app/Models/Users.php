<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Users extends Authenticatable implements JwtSubject
{
    use HasFactory;
    use Notifiable;
    use SoftDeletes;
    use HasUuids;

    protected $table = 'users';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'user_id';

    protected $fillable = [
        'username',
        'email',
        'password',
        'phone',
        'role_id',
        'user_type',
        'avatar',
        'bio',
        'is_active',
    ];

    protected $casts = [
        'role_id' => 'string',
        'is_active' => 'boolean',
        'created_at' => 'datetime:Y-m-d H:i',
        'updated_at' => 'datetime:Y-m-d H:i',
        'deleted_at' => 'datetime:Y-m-d H:i',
    ];
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }

    public function location()
    {
        return $this->hasMany(Location::class, 'user_id', 'user_id');
    }

    public function userBadge()
    {
        return $this->hasMany(UserBadge::class, 'user_id', 'user_id');
    }

    public function donationRequest()
    {
        return $this->hasMany(DonationRequest::class, 'user_id', 'user_id');
    }

    public function donationRequestValidation()
    {
        return $this->hasMany(DonationRequestValidation::class, 'admin_id', 'user_id');
    }

    public function donation()
    {
        return $this->hasMany(Donation::class, 'user_id', 'user_id');
    }

    public function messageSender()
    {
        return $this->hasMany(Message::class, 'sender_id', 'user_id');
    }

    public function messageReceiver()
    {
        return $this->hasMany(Message::class, 'receiver_id', 'user_id');
    }

    public function notification()
    {
        return $this->hasMany(Notification::class, 'user_id', 'user_id');
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
