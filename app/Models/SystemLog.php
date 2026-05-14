<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    protected $fillable = [
        'user_id',
        'event_type',
        'action',
        'description',
        'severity',
        'metadata',
        'ip_address',
        'is_archived'
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_archived' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
