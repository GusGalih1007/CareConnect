<?php

namespace App\Models;

use App\Enum\FinancialRequestStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinancialRequest extends Model
{
    use SoftDeletes;
    use HasUuids;

    protected $tsble = 'financial_requests';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'financial_request_id';

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'target_amount',
        'collected_amount',
        'deadline',
        'status',
        'proof_file'
    ];

    protected $casts = [
        'user_id' => 'string',
        'deadline' => 'date:Y-m-d',
        'target_amount' => 'decimal:2',
        'collected_amount' => 'decimal:2',
        'status' => FinancialRequestStatus::class,
        'proof_file' => 'json',
        'created_at' => 'datetime:Y-m-d H:i',
        'updated_at' => 'datetime:Y-m-d H:i',
        'deleted_at' => 'datetime:Y-m-d H:i',
    ];

    public function donationFinancial()
    {
        return $this->hasMany(DonationFinancial::class, 'financial_request_id', 'financial_request_id');
    }
}
