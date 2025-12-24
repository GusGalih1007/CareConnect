<?php

namespace App\Models;

use App\Enum\DonationCondition;
use App\Enum\DonationStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DonationItems extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'donation_items';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'donation_item_id';

    protected $fillable = [
        'donation_id',
        'category_id',
        'item_name',
        'description',
        'quantity',
        'condition',
        'status',
        'reserved_quantity',
        'images'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'reserved_quantity' => 'integer',
        'donation_id' => 'string',
        'category_id' => 'string',
        'condition' => DonationCondition::class,
        'status' => DonationStatus::class,
        'images' => 'json',
        'created_at' => 'datetime:Y-m-d H:i',
        'updated_at' => 'datetime:Y-m-d H:i',
        'deleted_at' => 'datetime:Y-m-d H:i',
    ];

    public function donation()
    {
        return $this->belongsTo(Donation::class, 'donation_id', 'donation_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    public function itemMatches()
    {
        return $this->hasMany(DonationItemMatch::class, 'donation_item_id', 'donation_item_id');
    }

    // Helper methods
    public function getAvailableQuantityAttribute()
    {
        return $this->quantity - $this->reserved_quantity;
    }

    public function isAvailable()
    {
        return $this->status == DonationStatus::Available && $this->getAvailableQuantityAttribute() > 0;
    }

    public function reserveQuantity($quantity)
    {
        if ($quantity > $this->getAvailableQuantityAttribute()) {
            return false;
        }

        $this->reserved_quantity += $quantity;
        if ($this->reserved_quantity >= $this->quantity) {
            $this->status = DonationStatus::Reserved;
        }
        $this->save();

        return true;
    }

    public function releaseQuantity($quantity)
    {
        $this->reserved_quantity = max(0, $this->reserved_quantity - $quantity);
        if ($this->reserved_quantity < $this->quantity) {
            $this->status = DonationStatus::Available;
        }
        $this->save();
    }
}
