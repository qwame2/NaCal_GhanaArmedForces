<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\InventoryBatch;

class EditRequest extends Model
{
    protected static function booted()
    {
        static::saved(function () {
            Setting::clearInventoryCache();
        });
        static::deleted(function () {
            Setting::clearInventoryCache();
        });
    }

    protected $fillable = [
        'user_id',
        'item_type',
        'item_id',
        'request_type',
        'reason',
        'status',
        'payload',
        'original_payload',
        'approved_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function batch()
    {
        return $this->belongsTo(InventoryBatch::class, 'item_id');
    }
}
