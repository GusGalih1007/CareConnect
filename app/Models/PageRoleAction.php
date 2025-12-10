<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PageRoleAction extends Model
{
    use HasUuids;
    protected $table = 'page_role_actions';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'page_role_actions_id';
    protected $fillable = [
        'page_code',
        'role_id',
        'page_name',
        'action'
    ];

    protected $casts = [
        'page_code' => 'string',
        'role_id' => 'string',
        'action' => 'json',
        'created_at' => 'datetime:Y-m-d H:i',
        'updated_at' => 'datetime:Y-m-d H:i',
    ];

    public function page()
    {
        return $this->belongsTo(Page::class, 'page_code', 'page_code');
    }
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }
}
