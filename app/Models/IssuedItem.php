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

    /**
     * Check if this specific issued item is overdue for return.
     */
    public function isOverdue()
    {
        if (!$this->issuance || $this->issuance->issuance_type !== 'Temporary' || $this->quantity <= 0) {
            return false;
        }

        $requisition = $this->issuance->requisition;
        if (!$requisition) {
            return false;
        }

        if (preg_match('/\[Expected Return Date:\s*([^\]]+)\]/i', $requisition->purpose, $matches)) {
            try {
                $returnDate = \Carbon\Carbon::parse(trim($matches[1]))->startOfDay();
                $today = \Carbon\Carbon::now()->startOfDay();
                return $today->gt($returnDate);
            } catch (\Exception $e) {
                return false;
            }
        }

        return false;
    }
}
