<?php

namespace App\Models;

use App\Enum\DonationRequestCondition;
use App\Enum\DonationRequestPriority;
use App\Enum\DonationRequestStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DonationRequestItems extends Model
{
    use SoftDeletes;
    use HasUuids;

    protected $table = 'donation_request_items';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'donation_request_item_id';

    protected $fillable = [
        'donation_request_id',
        'category_id',
        'item_name',
        'description',
        'quantity',
        'preferred_condition',
        'priority',
        'status',
        'fulfilled_quantity'
    ];

    protected $casts = [
        'donation_request_id' => 'string',
        'category_id' => 'string',
        'preferred_condition' => DonationRequestCondition::class,
        'priority' => DonationRequestPriority::class,
        'status' => DonationRequestStatus::class,
        'created_at' => 'datatime:Y-m-d H:i',
        'updated_at' => 'datatime:Y-m-d H:i',
        'deleted_at' => 'datatime:Y-m-d H:i',
    ];

    public function donationRequest()
    {
        return $this->belongsTo(DonationRequest::class, 'donation_request_id', 'donation_request_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }
}
