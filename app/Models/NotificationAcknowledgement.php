<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationAcknowledgement extends Model
{
    protected $fillable = [
        'user_id',
        'item_description',
        'alert_type',
        'acknowledged_at'
    ];

    protected $casts = [
        'acknowledged_at' => 'datetime'
    ];
}
