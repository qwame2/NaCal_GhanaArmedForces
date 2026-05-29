<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'group', 'description'];

    protected static $cachedSettings = [];

    protected static function booted()
    {
        static::saved(function () {
            self::$cachedSettings = [];
        });
        static::deleted(function () {
            self::$cachedSettings = [];
        });
    }

    /**
     * Get a setting value by key.
     */
    public static function get($key, $default = null)
    {
        if ($key === 'suppliers_registry') {
            if (\Illuminate\Support\Facades\Schema::hasTable('suppliers')) {
                return \App\Models\Supplier::all()->keyBy('name')->map(function($supplier) {
                    return [
                        'delivery_person' => $supplier->delivery_person,
                        'phone' => $supplier->phone,
                        'email' => $supplier->email,
                        'address' => $supplier->address,
                        'desc' => $supplier->desc
                    ];
                })->toArray();
            }
        }

        if (array_key_exists($key, self::$cachedSettings)) {
            return self::$cachedSettings[$key];
        }

        $setting = self::where('key', $key)->first();
        if (!$setting) {
            return $default;
        }

        $value = $setting->value;
        switch ($setting->type) {
            case 'boolean':
                $value = filter_var($setting->value, FILTER_VALIDATE_BOOLEAN);
                break;
            case 'integer':
                $value = (int) $setting->value;
                break;
            case 'json':
                $value = json_decode($setting->value, true);
                break;
        }

        self::$cachedSettings[$key] = $value;
        return $value;
    }

    /**
     * Set a setting value by key.
     */
    public static function set($key, $value, $type = 'string', $group = 'general', $description = null)
    {
        if (is_array($value) || is_object($value)) {
            $value = json_encode($value);
            $type = 'json';
        }

        return self::updateOrCreate(
            ['key' => $key],
            [
                'value' => (string) $value,
                'type' => $type,
                'group' => $group,
                'description' => $description
            ]
        );
    }

    /**
     * Get all inventory categories.
     */
    public static function getCategories()
    {
        $defaultCategories = [
            'A' => 'Stationary',
            'B' => 'Cleaning',
            'C' => 'IT & Acc.',
            'D' => 'Transport',
            'E' => 'Safety',
            'G' => 'Pharmacy',
            'J' => 'Equipment'
        ];

        $stored = self::get('inventory_categories', $defaultCategories);
        return is_array($stored) ? $stored : json_decode($stored, true);
    }

    /**
     * Add a new inventory category.
     */
    public static function addCategory($code, $name)
    {
        $categories = self::getCategories();
        $categories[strtoupper($code)] = $name;
        self::set('inventory_categories', $categories, 'json', 'inventory', 'Dynamic inventory categories');
    }

    /**
     * Remove an inventory category.
     */
    public static function removeCategory($code)
    {
        $categories = self::getCategories();
        $code = strtoupper($code);
        if (isset($categories[$code])) {
            unset($categories[$code]);
            self::set('inventory_categories', $categories, 'json', 'inventory', 'Dynamic inventory categories');
        }
    }

    /**
     * Get the threshold for a specific item, falling back to global setting.
     */
    public static function getItemThreshold($description, $category = null)
    {
        $rules = self::get('item_threshold_rules', []);
        $descLower = strtolower(trim($description));

        foreach ($rules as $keyword => $rule) {
            if (str_contains($descLower, strtolower(trim($keyword)))) {
                $ruleCat = $rule['category'] ?? null;
                // Match category if specified
                if ($ruleCat && $category && $ruleCat !== $category) {
                    continue;
                }
                return (int)($rule['threshold'] ?? $rule);
            }
        }

        // Fallback to global threshold
        return (int)self::get('low_stock_threshold', 100);
    }

    /**
     * Get the unit for a specific item based on keyword rules.
     */
    public static function getItemUnit($description)
    {
        $rules = self::get('item_unit_rules', []);
        $descLower = strtolower(trim($description));

        foreach ($rules as $keyword => $rule) {
            if (str_contains($descLower, strtolower(trim($keyword)))) {
                return is_array($rule) ? ($rule['unit'] ?? 'units') : $rule;
            }
        }

        return 'units';
    }

    /**
     * Get the request limit for a specific item.
     */
    public static function getItemRequestLimit($description, $category = null)
    {
        $limits = self::get('item_request_limits', []);
        $descLower = strtolower(trim($description));

        foreach ($limits as $keyword => $rule) {
            if (str_contains($descLower, strtolower(trim($keyword)))) {
                $ruleCat = $rule['category'] ?? null;
                if ($ruleCat && $category && $ruleCat !== $category) {
                    continue;
                }
                return (int)($rule['limit'] ?? $rule);
            }
        }
        return null;
    }

    /**
     * Get the available stock for an item after applying the request limit.
     */
    public static function getAvailableStock($description, $physicalStock, $category = null)
    {
        $limit = self::getItemRequestLimit($description, $category);
        if ($limit === null) {
            return $physicalStock;
        }

        // Find the matched keyword rule to use for counting
        $limits = self::get('item_request_limits', []);
        $descLower = strtolower(trim($description));
        $matchedKeyword = null;
        foreach ($limits as $keyword => $rule) {
            if (str_contains($descLower, strtolower(trim($keyword)))) {
                $ruleCat = $rule['category'] ?? null;
                if ($ruleCat && $category && $ruleCat !== $category) {
                    continue;
                }
                $matchedKeyword = $keyword;
                break;
            }
        }

        if (!$matchedKeyword) {
            return $physicalStock;
        }

        $keywordLower = strtolower(trim($matchedKeyword));
        $query = \App\Models\StoreRequisitionItem::join('store_requisitions', 'store_requisition_items.requisition_id', '=', 'store_requisitions.id')
            ->whereIn('store_requisitions.status', ['approved', 'partially_approved']);

        $originalSum = (float) $query->clone()
            ->where(\DB::raw('LOWER(store_requisition_items.description)'), 'LIKE', '%' . $keywordLower . '%')
            ->sum('store_requisition_items.quantity_approved');

        $alternativeSum = (float) $query->clone()
            ->where(\DB::raw('LOWER(store_requisition_items.alternative_description)'), 'LIKE', '%' . $keywordLower . '%')
            ->sum('store_requisition_items.alternative_quantity_approved');

        $givenOut = $originalSum + $alternativeSum;
        $remainingLimit = max(0, $limit - $givenOut);

        return min($physicalStock, $remainingLimit);
    }

    /**
     * Robust expected return date parser supporting both 2-digit and 4-digit years in d/m/y format.
     */
    public static function parseExpectedReturnDate($dateStr)
    {
        $dateStr = trim(str_replace('/', '-', $dateStr));
        $parts = explode('-', $dateStr);
        if (count($parts) === 3) {
            $day = intval($parts[0]);
            $month = intval($parts[1]);
            $year = intval($parts[2]);
            
            if ($year < 100) {
                $year += 2000;
            }
            
            try {
                return \Carbon\Carbon::createFromDate($year, $month, $day)->startOfDay();
            } catch (\Exception $e) {
                // Fallback to normal parsing if creation fails
            }
        }
        
        return \Carbon\Carbon::parse($dateStr)->startOfDay();
    }
}

