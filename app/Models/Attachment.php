<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attachment extends Model
{
    use SoftDeletes;
    use HasUuids;

    protected $table = 'attachments';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'attachment_id';

    protected $fillable = [
        'owner_type',
        'owner_id',
        'path',
        'meta'
    ];

    protected $casts = [
        'owner_id' => 'string',
        'meta' => 'json',
        'created_at' => 'datetime:Y-m-d H:i',
        'updated_at' => 'datetime:Y-m-d H:i',
        'deleted_at' => 'datetime:Y-m-d H:i',
    ];

    public function donationRequest()
    {
        return $this->belongsTo(DonationRequest::class, 'owner_id', 'donation_request_id');
    }
}
