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

    /**
     * Check if there are overdue temporary loans for this item description & category.
     */
    public function hasOverdueTemporaryLoan()
    {
        $category = $this->ledge_category ?? ($this->batch?->ledge_category ?? 'A');
        
        $activeLoans = \App\Models\IssuedItem::join('issuances', 'issued_items.issuance_id', '=', 'issuances.id')
            ->join('store_requisitions', 'issuances.requisition_id', '=', 'store_requisitions.id')
            ->select('store_requisitions.purpose')
            ->where('issuances.issuance_type', 'Temporary')
            ->where('issued_items.quantity', '>', 0)
            ->where(\DB::raw('TRIM(issued_items.description)'), trim($this->description))
            ->where('issued_items.ledge_category', $category)
            ->get();

        foreach ($activeLoans as $loan) {
            if (preg_match('/\[Expected Return Date:\s*([^\]]+)\]/i', $loan->purpose, $matches)) {
                try {
                    $returnDate = \Carbon\Carbon::parse(trim($matches[1]))->startOfDay();
                    $today = \Carbon\Carbon::now()->startOfDay();
                    if ($today->gt($returnDate)) {
                        return true;
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
        }

        return false;
    }
}
