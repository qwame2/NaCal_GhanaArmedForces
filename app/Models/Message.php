<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'message',
        'attachment',
        'attachment_name',
        'read_at',
        'is_automated',
        'edit_request_id',
        'is_archived'
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'is_archived' => 'boolean'
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function editRequest()
    {
        return $this->belongsTo(EditRequest::class, 'edit_request_id');
    }
}
