<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnedItem extends Model
{
    use HasFactory;

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
        'issued_item_id',
        'returned_qty',
        'return_date',
        'remarks'
    ];

    public function issuedItem()
    {
        return $this->belongsTo(IssuedItem::class, 'issued_item_id');
    }
}
