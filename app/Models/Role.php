<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Role extends Model
{
    use SoftDeletes;
    use HasUuids;

    protected $table = 'roles';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $primaryKey = 'role_id';

    protected $fillable = [
        'role_name',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i',
        'updated_at' => 'datetime:Y-m-d H:i',
        'deleted_at' => 'datetime:Y-m-d H:i',
    ];

    public function user()
    {
        return $this->hasMany(Users::class, 'role_id', 'role_id');
    }

    public function pageRoleActionRole()
    {
        return $this->hasMany(PageRoleAction::class, 'role_id', 'role_id');
    }
}
