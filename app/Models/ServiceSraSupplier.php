<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ServiceSraSupplier extends Model
{
    use HasFactory;

    protected $table = 'service_sra_suppliers';

    protected $fillable = [
        'name',
        'address',
        'phone',
        'contact_person',
        'notes',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sras()
    {
        return $this->hasMany(ServiceSra::class, 'supplier_name', 'name');
    }

    public static function getActiveList()
    {
        return static::where('is_active', true)
            ->orderBy('name')
            ->get();
    }
}
