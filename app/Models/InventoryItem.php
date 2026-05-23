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
        'remarks',
        'location'
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

    /**
     * Check if there are active temporary loans for this item description & category.
     */
    public function hasActiveTemporaryLoan()
    {
        $category = $this->ledge_category ?? ($this->batch?->ledge_category ?? 'A');
        
        $hasActiveLoan = \App\Models\IssuedItem::join('issuances', 'issued_items.issuance_id', '=', 'issuances.id')
            ->where('issuances.issuance_type', 'Temporary')
            ->where('issued_items.quantity', '>', 0)
            ->where(\DB::raw('TRIM(issued_items.description)'), trim($this->description))
            ->where('issued_items.ledge_category', $category)
            ->exists();

        if ($hasActiveLoan) {
            return true;
        }

        // Fallback for historical data
        return ((float)$this->stock_balance - (float)$this->qty) > 0;
    }
}
