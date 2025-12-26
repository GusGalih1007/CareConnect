<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Users extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasUuids;
    use Notifiable;
    use SoftDeletes;

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
        'email_verified_at',
    ];

    protected $casts = [
        'role_id' => 'string',
        'is_active' => 'boolean',
        'created_at' => 'datetime:Y-m-d H:i',
        'updated_at' => 'datetime:Y-m-d H:i',
        'deleted_at' => 'datetime:Y-m-d H:i',
        'email_verified_at' => 'datetime:Y-m-d H:i',
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

    public function requestValidation()
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

    public function volunteerTask()
    {
        return $this->hasMany(VolunteerTask::class, 'volunteer_id', 'user_id');
    }

    public function financialRequest()
    {
        return $this->hasMany(FinancialRequest::class, 'user_id', 'user_id');
    }

    public function donationFinacial()
    {
        return $this->hasMany(DonationFinancial::class, 'donor_id', 'user_id');
    }

    public function financialDisbursement()
    {
        return $this->hasMany(FinancialDisbursement::class, 'admin_id', 'user_id');
    }

    public function adminAction()
    {
        return $this->hasMany(AdminAction::class, 'admin_id', 'user_id');
    }

    public function otpCode()
    {
        return $this->hasMany(OtpCode::class.'user_id', 'user_id');
    }

    public function isAdmin()
    {
        return $this->role->role_name === 'Super Admin' || $this->role->role_name === 'admin';
    }

    public function isVolunteer()
    {
        return $this->role->role_name === 'volunteer';
    }

    public function pendingMatches()
    {
        return $this->hasManyThrough(
            DonationItemMatch::class,
            DonationRequestItems::class,
            'donation_request_id',
            'donation_request_item_id',
            'donation_request_id',
            'donation_request_item_id'
        )->where('status', 'pending')
            ->orWhereHas('donationItem.donation', function ($q) {
                $q->where('user_id', $this->user_id);
            })->where('status', 'pending');
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    // public function getJWTIdentifier()
    // {
    //     return $this->getKey();
    // }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    // public function getJWTCustomClaims()
    // {
    //     return [];
    // }
}
