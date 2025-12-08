<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Role extends Model
{
    use SoftDeletes;

    protected $table = 'roles';
    protected $primaryKey = 'role_id';
    protected $fillable = [
        'role_code',
        'role_name',
        'description'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i',
        'updated_at' => 'datetime:Y-m-d H:i',
        'deleted_at' => 'datetime:Y-m-d H:i',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->role_code))
            {
                $model->role_code = (string) Str::uuid();
            }
        });
    }

    public function user()
    {
        return $this->hasMany(Users::class, 'role_id', 'role_id');
    }
    public function pageRoleActionRole()
    {
        return $this->hasMany(PageRoleAction::class, 'role_id', 'role_id');
    }
}
