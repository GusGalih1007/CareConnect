<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Page extends Model
{
    use SoftDeletes;
    use HasUuids;

    protected $table = 'pages';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $primaryKey = 'page_code';

    protected $fillable = [
        'page_name',
        'action',
        'desciption',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i',
        'updated_at' => 'datetime:Y-m-d H:i',
        'deleted_at' => 'datetime:Y-m-d H:i',
        'action' => 'json',
    ];

    public function pageRoleActionCode()
    {
        return $this->hasMany(PageRoleAction::class, 'page_code', 'page_code');
    }
}
