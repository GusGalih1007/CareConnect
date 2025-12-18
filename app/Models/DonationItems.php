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
}
