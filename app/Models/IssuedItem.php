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

        $returnDate = null;
        if (preg_match('/\[Expected Return Date:\s*([^\]]+)\]/i', $requisition->purpose, $matches)) {
            try {
                $returnDate = \App\Models\Setting::parseExpectedReturnDate(trim($matches[1]))->format('Y-m-d');
            } catch (\Exception $e) {
                // Ignore
            }
        }

        if (!$returnDate) {
            $returnDate = $requisition->created_at->format('Y-m-d');
        }

        $today = \Carbon\Carbon::now()->format('Y-m-d');
        return $today >= $returnDate;
    }

    /**
     * Get the parsed expected return date for this issued item.
     */
    public function getExpectedReturnDate()
    {
        if (!$this->issuance || $this->issuance->issuance_type !== 'Temporary' || $this->quantity <= 0) {
            return null;
        }

        $requisition = $this->issuance->requisition;
        if (!$requisition) {
            return null;
        }

        if (preg_match('/\[Expected Return Date:\s*([^\]]+)\]/i', $requisition->purpose, $matches)) {
            try {
                return \App\Models\Setting::parseExpectedReturnDate(trim($matches[1]));
            } catch (\Exception $e) {
                // Ignore
            }
        }

        return $requisition->created_at;
    }
}
