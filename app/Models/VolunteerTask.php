<?php

namespace App\Models;

use App\Enum\VolunteerTaskStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VolunteerTask extends Model
{
    use SoftDeletes;

    protected $table = 'volunteer_tasks';
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
        'proof' => 'json',
        'status' => VolunteerTaskStatus::class,
        'pickup_time' => 'datetime:Y-m-d H:i',
        'delivered_at' => 'datetime:Y-m-d H:i',
        'created_at' => 'datetime:Y-m-d H:i',
        'updated_at' => 'datetime:Y-m-d H:i',
        'deleted_at' => 'datetime:Y-m-d H:i',
    ];
}
