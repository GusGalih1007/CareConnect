<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attachment extends Model
{
    use SoftDeletes;

    protected $table = 'attachments';
    protected $primaryKey = 'attachment_id';

    protected $fillable = [
        'owner_type',
        'owner_id',
        'path',
        'meta'
    ];

    protected $casts = [
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
