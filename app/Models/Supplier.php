<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'delivery_person',
        'delivery_phone',
        'phone',
        'email',
        'address',
        'desc'
    ];

    protected static function booted()
    {
        static::saved(function () {
            \Illuminate\Support\Facades\Cache::forget('setting_suppliers_registry');
            Setting::clearInventoryCache();
        });
        static::deleted(function () {
            \Illuminate\Support\Facades\Cache::forget('setting_suppliers_registry');
            Setting::clearInventoryCache();
        });
    }
}
