<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EditRequest extends Model
{
    protected $fillable = [
        'user_id',
        'item_type',
        'item_id',
        'request_type',
        'reason',
        'status',
        'payload',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
