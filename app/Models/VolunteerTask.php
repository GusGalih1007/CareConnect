<?php

namespace App\Models;

use App\Enum\VolunteerTaskStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VolunteerTask extends Model
{
    use SoftDeletes;
    use HasUuids;

    protected $table = 'volunteer_tasks';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'volunteer_task_id';
    protected $fillable = [
        'volunteer_id',
        'donation_id',
        'status',
        'pickup_time',
        'delivered_at',
        'proof',
    ];

    protected $casts = [
        'volunteer_id' => 'string',
        'donation_id' => 'string',
        'proof' => 'json',
        'status' => VolunteerTaskStatus::class,
        'pickup_time' => 'datetime:Y-m-d H:i',
        'delivered_at' => 'datetime:Y-m-d H:i',
        'created_at' => 'datetime:Y-m-d H:i',
        'updated_at' => 'datetime:Y-m-d H:i',
        'deleted_at' => 'datetime:Y-m-d H:i',
    ];

    public function volunteer()
    {
        return $this->belongsTo(Users::class, 'volunteer_id', 'user_id');
    }
    public function donation()
    {
        return $this->belongsTo(Donation::class, 'donation_id', 'donation_id');
    }
}
