<?php

namespace App\Models;

use App\Enum\DonationFinancialStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DonationFinancial extends Model
{
    use SoftDeletes;
    use HasUuids;

    protected $table = 'donation_financials';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'donation_financial_id';

    protected $fillable = [
        'donor_id',
        'financial_request_id',
        'amount',
        'payment_gateway_id',
        'status',
        'gateway_reference',
        'proof'
    ];

    protected $casts = [
        'donor_id' => 'string',
        'financial_request_id' => 'string',
        'amount' => 'decimal:2',
        'payment_gateway_id' => 'string',
        'status' => DonationFinancialStatus::class,
        'proof' => 'json',
        'created_at' => 'datetime:Y-m-d H:i',
        'updated_at' => 'datetime:Y-m-d H:i',
        'deleted_at' => 'datetime:Y-m-d H:i',
    ];

    public function donor()
    {
        return $this->belongsTo(Users::class, 'donor_id', 'user_id');
    }
    public function financialRequest()
    {
        return $this->belongsTo(FinancialRequest::class, 'financial_request_id', 'financial_request_id');
    }
    public function paymetnGateway()
    {
        return $this->belongsTo(PaymentGateway::class, 'payment_gateway_id', 'payment_gateway_id');
    }
}
