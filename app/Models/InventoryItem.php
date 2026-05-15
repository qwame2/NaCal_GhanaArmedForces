<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'description',
        'unit',
        'stock_balance',
        'qty',
        'variance',
        'remarks'
    ];

    public function batch()
    {
        return $this->belongsTo(InventoryBatch::class, 'batch_id');
    }

    /**
     * Dynamically override the item's unit based on global rules.
     */
    public function getUnitAttribute($value)
    {
        // Try to get a matching unit from the rules
        $dynamicUnit = Setting::getItemUnit($this->description);
        
        // If it returns 'units' (the default) but we have a stored value, use the stored value
        // unless the user explicitly wants to force 'units'.
        // Actually, if a rule matches, Setting::getItemUnit returns the rule's unit.
        // If no rule matches, it returns 'units'.
        
        if ($dynamicUnit !== 'units') {
            return $dynamicUnit;
        }

        return $value ?: 'units';
    }
}
