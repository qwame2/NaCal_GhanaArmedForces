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

    protected static function booted()
    {
        static::saved(function () {
            Setting::clearInventoryCache();
        });
        static::deleted(function () {
            Setting::clearInventoryCache();
        });
    }


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
            ->where(\DB::raw('LOWER(TRIM(issued_items.description))'), '=', strtolower(trim($this->description)))
            ->where('issued_items.ledge_category', $category)
            ->exists();

        if ($hasActiveLoan) {
            return true;
        }

        // Fallback for historical data
        return ((float)$this->stock_balance - (float)$this->qty) > 0;
    }

    public function hasOverdueTemporaryLoan()
    {
        $category = $this->ledge_category ?? ($this->batch?->ledge_category ?? 'A');
        
        $activeLoans = \App\Models\IssuedItem::join('issuances', 'issued_items.issuance_id', '=', 'issuances.id')
            ->join('store_requisitions', 'issuances.requisition_id', '=', 'store_requisitions.id')
            ->select(
                'issued_items.id',
                'issued_items.quantity',
                'store_requisitions.purpose',
                'store_requisitions.created_at',
                \DB::raw('(SELECT COALESCE(SUM(returned_qty), 0) FROM returned_items WHERE returned_items.issued_item_id = issued_items.id) as total_returned')
            )
            ->where('issuances.issuance_type', 'Temporary')
            ->where('issued_items.quantity', '>', 0)
            ->where(\DB::raw('LOWER(TRIM(issued_items.description))'), '=', strtolower(trim($this->description)))
            ->where('issued_items.ledge_category', $category)
            ->get();

        foreach ($activeLoans as $loan) {
            // Check if fully returned
            $returnedQty = floatval($loan->total_returned);
            if ($loan->quantity <= 0 || $returnedQty >= $loan->quantity) {
                continue;
            }

            $returnDate = null;
            if (preg_match('/\[Expected Return Date:\s*([^\]]+)\]/i', $loan->purpose, $matches)) {
                try {
                    $returnDate = \App\Models\Setting::parseExpectedReturnDate(trim($matches[1]))->format('Y-m-d');
                } catch (\Exception $e) {
                    continue;
                }
            }
            if (!$returnDate) {
                $returnDate = \Carbon\Carbon::parse($loan->created_at)->format('Y-m-d');
            }
            $today = \Carbon\Carbon::now()->format('Y-m-d');
            if ($today >= $returnDate) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all return dates for this item description and category.
     */
    public function getReturnDates()
    {
        $category = $this->ledge_category ?? ($this->batch?->ledge_category ?? 'A');
        
        return \App\Models\ReturnedItem::join('issued_items', 'returned_items.issued_item_id', '=', 'issued_items.id')
            ->where(\DB::raw('TRIM(issued_items.description)'), trim($this->description))
            ->where('issued_items.ledge_category', $category)
            ->orderBy('returned_items.return_date', 'desc')
            ->pluck('returned_items.return_date')
            ->map(function($date) {
                return \Carbon\Carbon::parse($date)->format('d/m/y');
            })
            ->unique()
            ->values();
    }

    public function getExpectedReturnDates()
    {
        $category = $this->ledge_category ?? ($this->batch?->ledge_category ?? 'A');
        
        $activeLoans = \App\Models\IssuedItem::join('issuances', 'issued_items.issuance_id', '=', 'issuances.id')
            ->join('store_requisitions', 'issuances.requisition_id', '=', 'store_requisitions.id')
            ->select(
                'issued_items.id',
                'issued_items.quantity',
                'store_requisitions.purpose',
                'store_requisitions.created_at',
                'store_requisitions.department',
                \DB::raw('(SELECT COALESCE(SUM(returned_qty), 0) FROM returned_items WHERE returned_items.issued_item_id = issued_items.id) as total_returned')
            )
            ->where('issuances.issuance_type', 'Temporary')
            ->where('issued_items.quantity', '>', 0)
            ->where(\DB::raw('LOWER(TRIM(issued_items.description))'), '=', strtolower(trim($this->description)))
            ->where('issued_items.ledge_category', $category)
            ->get();

        $dates = [];
        foreach ($activeLoans as $loan) {
            // Check if fully returned
            $returnedQty = floatval($loan->total_returned);
            if ($loan->quantity <= 0 || $returnedQty >= $loan->quantity) {
                continue;
            }

            $dateObj = null;
            if (preg_match('/\[Expected Return Date:\s*([^\]]+)\]/i', $loan->purpose, $matches)) {
                try {
                    $dateObj = \App\Models\Setting::parseExpectedReturnDate(trim($matches[1]));
                } catch (\Exception $e) {
                    continue;
                }
            }
            if (!$dateObj && $loan->created_at) {
                $dateObj = \Carbon\Carbon::parse($loan->created_at)->startOfDay();
            }
            if ($dateObj) {
                $dates[] = [
                    'formatted' => $dateObj->format('d/m/y'),
                    'date_str' => $dateObj->format('Y-m-d'),
                    'department' => $loan->department
                ];
            }
        }

        // Sort by date string ascending
        usort($dates, function($a, $b) {
            return strcmp($a['date_str'], $b['date_str']);
        });

        $uniqueDates = collect($dates)->unique('formatted')->values();
        if ($uniqueDates->isEmpty()) {
            return collect();
        }

        // Return only the earliest expected return date to avoid listing multiple dates
        return collect([$uniqueDates->first()]);
    }
}

