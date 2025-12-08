<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Page extends Model
{
    use SoftDeletes;

    protected $table = 'pages';
    protected $primaryKey = 'page_id';
    protected $fillable = [
        'page_code',
        'page_name',
        'action',
        'desciption'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i',
        'updated_at' => 'datetime:Y-m-d H:i',
        'deleted_at' => 'datetime:Y-m-d H:i',
        'action' => 'json'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->page_code))
            {
                $model->page_code = (string) Str::uuid();
            }
        });
    }
    public function pageRoleActionCode()
    {
        return $this->hasMany(PageRoleAction::class, 'page_code', 'page_code');
    }
}
