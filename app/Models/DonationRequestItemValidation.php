<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enum\DonationRequestValidationStatus;

class DonationRequestItemValidation extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'request_item_validations';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'request_item_validation_id';
    protected $fillable = [
        'request_validation_id',
        'donation_request_item_id',
        'category_id',
        'admin_id',
        'status',
        'note',
        'evidence_files',
    ];
    protected $casts = [
        'request_validation_id' => 'string',
        'donation_request_item_id' => 'string',
        'category_id' => 'string',
        'admin_id' => 'string',
        'evidence_files' => 'json',
        'created_at' => 'datetime:Y-m-d H:i',
        'updated_at' => 'datetime:Y-m-d H:i',
        'deleted_at' => 'datetime:Y-m-d H:i',
        'status' => DonationRequestValidationStatus::class,
    ];
    
    public function admin()
    {
        return $this->belongsTo(Users::class, 'admin_id', 'user_id');
    }

    public function validation()
    {
        return $this->belongsTo(DonationRequestValidation::class, 'request_validation_id', 'request_validation_id');
    }

    public function requestItem()
    {
        return $this->belongsTo(DonationRequestItems::class, 'donation_request_item_id', 'donation_request_item_id');
    }

    public function category()
    {

        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }
}
