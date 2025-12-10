<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentGateway extends Model
{
    use SoftDeletes;
    use HasUuids;

    protected $table = 'payment_gateways';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'payment_gateway_id';

    protected $fillable = [
        'payment_name',
        'type'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i',
        'updated_at' => 'datetime:Y-m-d H:i',
        'deleted_at' => 'datetime:Y-m-d H:i',
    ];

    public function donationFinancial()
    {
        return $this->hasMany(DonationFinancial::class, 'payment_gateway_id', 'payment_gateway_id');
    }
}
