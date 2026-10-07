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
        'serial_number',
        'unit',
        'stock_balance',
        'qty',
        'variance',
        'remarks',
        'store_location',
        'book_qty',
        'discrepancy_explanation',
        'received_qty'
    ];

    protected $appends = ['original_received_qty'];

    /**
     * Get the immutable original received quantity entered at receiving time.
     */
    public function getOriginalReceivedQtyAttribute(): float
    {
        if (!is_null($this->received_qty) && (float)$this->received_qty > 0) {
            return (float)$this->received_qty;
        }

        // Look up the creation record in StockHistory
        $createHistory = \App\Models\StockHistory::where('inventory_item_id', $this->id)
            ->where('action', 'create')
            ->first();

        if ($createHistory) {
            $hQty = floatval(str_replace(',', '', $createHistory->new_qty ?: $createHistory->new_stock_balance ?: 0));
            if ($hQty > 0) {
                return $hQty;
            }
        }

        $q = floatval(str_replace(',', '', $this->qty ?? 0));
        if ($q > 0) {
            return $q;
        }

        if (!is_null($this->book_qty)) {
            $bq = floatval(str_replace(',', '', $this->book_qty));
            if ($bq > 0) {
                return $bq;
            }
        }

        return floatval(str_replace(',', '', $this->stock_balance ?? 0));
    }

    protected static function booted()
    {
        static::creating(function ($item) {
            if ($item->received_qty === null || $item->received_qty === '') {
                $raw = $item->qty ?? ($item->stock_balance ?? 0);
                $item->received_qty = floatval(str_replace(',', '', $raw));
            }
            if ($item->qty === null || $item->qty === '') {
                $item->qty = $item->received_qty;
            }
        });
        static::saved(function () {
            Setting::clearInventoryCache();
        });
        static::created(function ($item) {
            try {
                \App\Models\StockHistory::create([
                    'inventory_item_id' => $item->id,
                    'user_id' => auth()->id(),
                    'action' => 'create',
                    'new_description' => $item->description,
                    'new_unit' => $item->unit,
                    'new_qty' => $item->qty,
                    'new_stock_balance' => $item->stock_balance,
                    'new_variance' => $item->variance,
                ]);
            } catch (\Exception $e) {
                // Prevent model failures from breaking main execution flows
            }
        });
        static::updating(function ($item) {
            try {
                $monitored = ['description', 'unit', 'qty', 'stock_balance', 'variance'];
                $changed = false;
                foreach ($monitored as $field) {
                    if ($item->isDirty($field)) {
                        $changed = true;
                        break;
                    }
                }
                if ($changed) {
                    \App\Models\StockHistory::create([
                        'inventory_item_id' => $item->id,
                        'user_id' => auth()->id(),
                        'action' => 'update',
                        'old_description' => $item->getOriginal('description'),
                        'new_description' => $item->description,
                        'old_unit' => $item->getOriginal('unit'),
                        'new_unit' => $item->unit,
                        'old_qty' => $item->getOriginal('qty'),
                        'new_qty' => $item->qty,
                        'old_stock_balance' => $item->getOriginal('stock_balance'),
                        'new_stock_balance' => $item->stock_balance,
                        'old_variance' => $item->getOriginal('variance'),
                        'new_variance' => $item->variance,
                    ]);
                }
            } catch (\Exception $e) {
                // Prevent failures
            }
        });
        static::updated(function ($item) {
            if (static::$isSyncingSharedAttributes) {
                return;
            }

            $monitored = ['description', 'unit', 'store_location'];
            $changed = false;
            foreach ($monitored as $field) {
                if ($item->wasChanged($field)) {
                    $changed = true;
                    break;
                }
            }

            if ($changed) {
                static::$isSyncingSharedAttributes = true;
                try {
                    $oldDescription = $item->getOriginal('description') ?? $item->description;
                    $newDescription = $item->description;
                    $newUnit = $item->unit;
                    $newStoreLocation = $item->store_location;

                    $oldClean = trim(strtoupper($oldDescription));
                    $newClean = trim(strtoupper($newDescription));

                    $query = static::where('id', '!=', $item->id)
                        ->where(function($q) use ($oldClean, $newClean) {
                            if ($oldClean !== '') {
                                $q->whereRaw('TRIM(UPPER(description)) = ?', [$oldClean]);
                            }
                            if ($newClean !== '') {
                                $q->orWhereRaw('TRIM(UPPER(description)) = ?', [$newClean]);
                            }
                        });

                    $updates = [];
                    if (!empty($newDescription)) $updates['description'] = trim($newDescription);
                    if (!empty($newUnit)) $updates['unit'] = trim($newUnit);
                    if (!empty($newStoreLocation)) $updates['store_location'] = trim($newStoreLocation);

                    if (!empty($updates)) {
                        $query->update($updates);
                        Setting::clearInventoryCache();
                    }
                } catch (\Exception $e) {
                    // Prevent failures
                } finally {
                    static::$isSyncingSharedAttributes = false;
                }
            }
        });
        static::deleted(function ($item) {
            Setting::clearInventoryCache();
            try {
                \App\Models\StockHistory::create([
                    'inventory_item_id' => $item->id,
                    'user_id' => auth()->id(),
                    'action' => 'delete',
                    'old_description' => $item->description,
                    'old_unit' => $item->unit,
                    'old_qty' => $item->qty,
                    'old_stock_balance' => $item->stock_balance,
                    'old_variance' => $item->variance,
                ]);
            } catch (\Exception $e) {
                // Prevent failures
            }
        });
    }

    public static bool $isSyncingSharedAttributes = false;


    public function batch()
    {
        return $this->belongsTo(InventoryBatch::class, 'batch_id');
    }

    /**
     * Dynamically override the item's unit based on global rules.
     */
    public function getUnitAttribute($value)
    {
        if (!empty($value)) {
            return $value;
        }

        // Try to get a matching unit from the rules if not set
        $dynamicUnit = Setting::getItemUnit($this->description);
        if ($dynamicUnit !== 'units') {
            return $dynamicUnit;
        }

        return 'units';
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
        return false;
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

