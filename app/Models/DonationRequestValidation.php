<?php

namespace App\Models;

use App\Enum\DonationRequestValidationStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DonationRequestValidation extends Model
{
    use SoftDeletes;
    use HasUuids;

    protected $table = 'donation_request_validations';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'request_validation_id';
    protected $fillable = [
        'donation_request_id',
        'admin_id',
        'status',
        'note',
        'evidence_files',
    ];
    protected $casts = [
        'donation_request_id' => 'string',
        'admin_id' => 'string',
        'evidence_files' => 'json',
        'created_at' => 'datetime:Y-m-d H:i',
        'updated_at' => 'datetime:Y-m-d H:i',
        'deleted_at' => 'datetime:Y-m-d H:i',
        'status' => DonationRequestValidationStatus::class,
    ];

    public function request()
    {
        return $this->belongsTo(DonationRequest::class, 'donation_request_id', 'donation_request_id');
    }

    public function admin()
    {
        return $this->belongsTo(Users::class, 'admin_id', 'user_id');
    }

    public function items()
    {
        return $this->hasMany(DonationRequestItemValidation::class, 'request_validation_id', 'request_validation_id');
    }
}
