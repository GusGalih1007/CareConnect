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
        'quantity' => 'integer',
        'fulfilled_quantity' => 'integer',
        'donation_request_id' => 'string',
        'category_id' => 'string',
        'preferred_condition' => DonationRequestCondition::class,
        'priority' => DonationRequestPriority::class,
        'status' => DonationRequestStatus::class,
        'created_at' => 'datetime:Y-m-d H:i',
        'updated_at' => 'datetime:Y-m-d H:i',
        'deleted_at' => 'datetime:Y-m-d H:i',
    ];

    public function request()
    {
        return $this->belongsTo(DonationRequest::class, 'donation_request_id', 'donation_request_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    public function itemMatches()
    {
        return $this->hasMany(DonationItemMatch::class, 'donation_request_item_id', 'donation_request_item_id');
    }

    public function validation()
    {
        return $this->hasOne(DonationRequestItemValidation::class, 'donation_request_item_id', 'donation_request_item_id');
    }

    // Helper methods
    public function getRemainingQuantityAttribute()
    {
        return $this->quantity - $this->fulfilled_quantity;
    }

    public function isFulfilled()
    {
        return $this->fulfilled_quantity >= $this->quantity;
    }

    public function isPartiallyFulfilled()
    {
        return $this->fulfilled_quantity > 0 && $this->fulfilled_quantity < $this->quantity;
    }

    public function updateFulfilledQuantity($quantity)
    {
        $this->fulfilled_quantity += $quantity;
        if ($this->fulfilled_quantity >= $this->quantity) {
            $this->status = DonationRequestStatus::Fulfilled;
        } elseif ($this->fulfilled_quantity > 0) {
            $this->status = DonationRequestStatus::PartiallyFulfilled;
        }
        $this->save();
    }
}
