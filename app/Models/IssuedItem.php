<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IssuedItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'issuance_id',
        'description',
        'ledge_category',
        'quantity',
        'unit'
    ];

    public function issuance()
    {
        return $this->belongsTo(Issuance::class);
    }

    /**
     * Dynamically override the issued item's unit based on global rules.
     */
    public function getUnitAttribute($value)
    {
        $dynamicUnit = Setting::getItemUnit($this->description);
        if ($dynamicUnit !== 'units') {
            return $dynamicUnit;
        }
        return $value ?: 'units';
    }
}
