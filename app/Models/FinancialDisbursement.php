<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinancialDisbursement extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'financial_disbursements';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'financial_disburesment_id';
    protected $fillable = [
        'financial_request_id',
        'admin_id',
        'amount',
        'proof'
    ];

    protected $casts = [
        'financial_request_id' => 'string',
        'admin_id' => 'string',
        'proof' => 'json',
        'created_at' => 'datetime:Y-m-d H:i',
        'updated_at' => 'datetime:Y-m-d H:i',
        'deleted_at' => 'datetime:Y-m-d H:i',
    ];

    public function admin()
    {
        return $this->belongsTo(Users::class, 'admin_id', 'user_id');
    }
    public function financialRequest()
    {
        return $this->belongsTo(FinancialRequest::class, 'financial_request_id', 'financial_request_id');
    }
}
