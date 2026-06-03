<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Issuance extends Model
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
        'issuance_date',
        'beneficiary',
        'authority',
        'issuance_type',
        'requisition_id'
    ];

    public function items()
    {
        return $this->hasMany(IssuedItem::class);
    }

    public function requisition()
    {
        return $this->belongsTo(StoreRequisition::class, 'requisition_id');
    }
}
