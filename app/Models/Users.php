<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Str;

class Users extends Authenticatable Implements JwtSubject
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'user_id';
    protected $fillable = [
        'user_code',
        'username',
        'email',
        'password',
        'phone',
        'role_id',
        'user_type',
        'avatar',
        'bio',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime:Y-m-d H:i',
        'updated_at' => 'datetime:Y-m-d H:i',
        'deleted_at' => 'datetime:Y-m-d H:i',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->role_code))
            {
                $model->role_code = (string) Str::uuid();
            }
        });
    }

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
