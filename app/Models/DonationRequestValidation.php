<?php

namespace App\Models;

use App\Enum\DonationRequestValidationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DonationRequestValidation extends Model
{
    use SoftDeletes;

    protected $table = 'donation_request_validations';
    protected $primaryKey = 'donation_request_validation_id';
    protected $fillable = [
        'donation_request_id',
        'admin_id',
        'status',
        'note',
        'evidence_files',
    ];
    protected $casts = [
        'evidence_files' => 'json',
        'created_at' => 'datetime:Y-m-d H:i',
        'updated_at' => 'datetime:Y-m-d H:i',
        'deleted_at' => 'datetime:Y-m-d H:i',
        'status' => DonationRequestValidationStatus::class,
    ];

    public function donationRequest()
    {
        return $this->belongsTo(DonationRequest::class, 'donation_request_id', 'donation_request_id');
    }
    public function admin()
    {
        return $this->belongsTo(Users::class, 'admin_id', 'user_id');
    }
}
