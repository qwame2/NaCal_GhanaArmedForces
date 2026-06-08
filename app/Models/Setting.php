<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'group', 'description'];

    protected static $cachedSettings = [];
    protected static $itemThresholdCache = [];
    protected static $itemUnitCache = [];
    protected static $itemRequestLimitCache = [];

    public static function clearInventoryCache()
    {
        self::$itemThresholdCache = [];
        self::$itemUnitCache = [];
        self::$itemRequestLimitCache = [];
        \Illuminate\Support\Facades\Cache::forget('dashboard_metrics_data');
        \Illuminate\Support\Facades\Cache::forget('low_stock_items_list');
        \Illuminate\Support\Facades\Cache::forget('item_aggregates_list');
        \Illuminate\Support\Facades\Cache::forget('global_low_stock_alerts');
        \Illuminate\Support\Facades\Cache::forget('global_expired_alerts');
    }

    protected static function booted()
    {
        static::saved(function ($setting) {
            self::$cachedSettings = [];
            self::$itemThresholdCache = [];
            self::$itemUnitCache = [];
            self::$itemRequestLimitCache = [];
            \Illuminate\Support\Facades\Cache::forget('setting_' . $setting->key);
            if ($setting->key === 'suppliers_registry') {
                \Illuminate\Support\Facades\Cache::forget('setting_suppliers_registry');
            }
            self::clearInventoryCache();
        });
        static::deleted(function ($setting) {
            self::$cachedSettings = [];
            self::$itemThresholdCache = [];
            self::$itemUnitCache = [];
            self::$itemRequestLimitCache = [];
            \Illuminate\Support\Facades\Cache::forget('setting_' . $setting->key);
            if ($setting->key === 'suppliers_registry') {
                \Illuminate\Support\Facades\Cache::forget('setting_suppliers_registry');
            }
            self::clearInventoryCache();
        });
    }

    /**
     * Get a setting value by key.
     */
    public static function get($key, $default = null)
    {
        if ($key === 'suppliers_registry') {
            if (\Illuminate\Support\Facades\Schema::hasTable('suppliers')) {
                return \Illuminate\Support\Facades\Cache::remember('setting_suppliers_registry', 86400, function() {
                    return \App\Models\Supplier::all()->keyBy('name')->map(function($supplier) {
                        return [
                            'delivery_person' => $supplier->delivery_person,
                            'delivery_phone' => $supplier->delivery_phone,
                            'phone' => $supplier->phone,
                            'email' => $supplier->email,
                            'address' => $supplier->address,
                            'desc' => $supplier->desc
                        ];
                    })->toArray();
                });
            }
        }

        if (array_key_exists($key, self::$cachedSettings)) {
            return self::$cachedSettings[$key];
        }

        $value = \Illuminate\Support\Facades\Cache::remember('setting_' . $key, 86400, function() use ($key) {
            $setting = self::where('key', $key)->first();
            if (!$setting) {
                return ['__null__' => true];
            }

            $val = $setting->value;
            switch ($setting->type) {
                case 'boolean':
                    $val = filter_var($setting->value, FILTER_VALIDATE_BOOLEAN);
                    break;
                case 'integer':
                    $val = (int) $setting->value;
                    break;
                case 'json':
                    $val = json_decode($setting->value, true);
                    break;
            }
            return $val;
        });

        if (is_array($value) && isset($value['__null__'])) {
            $value = $default;
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
        $descClean = trim($description);
        $cacheKey = $descClean . '_' . ($category ?? '');
        if (isset(self::$itemThresholdCache[$cacheKey])) {
            return self::$itemThresholdCache[$cacheKey];
        }

        $rules = self::get('item_threshold_rules', []);
        if (is_string($rules)) {
            $rules = json_decode($rules, true);
        }
        if (!is_array($rules)) {
            $rules = [];
        }
        $descLower = strtolower($descClean);

        $threshold = null;
        foreach ($rules as $keyword => $rule) {
            $keywordLower = strtolower(trim($keyword));
            $pattern = '/\b' . preg_quote($keywordLower, '/') . '\b/i';
            if (preg_match($pattern, $descLower)) {
                $ruleCat = $rule['category'] ?? null;
                // Match category if specified
                if ($ruleCat && $category && strcasecmp(trim($ruleCat), trim($category)) !== 0) {
                    continue;
                }
                $threshold = (int)($rule['threshold'] ?? $rule);
                break;
            }
        }

        if ($threshold === null) {
            // Fallback to global threshold
            $threshold = (int)self::get('low_stock_threshold', 100);
        }

        self::$itemThresholdCache[$cacheKey] = $threshold;
        return $threshold;
    }

    /**
     * Get the unit for a specific item based on keyword rules.
     */
    public static function getItemUnit($description)
    {
        $descClean = trim($description);
        if (isset(self::$itemUnitCache[$descClean])) {
            return self::$itemUnitCache[$descClean];
        }

        $rules = self::get('item_unit_rules', []);
        if (is_string($rules)) {
            $rules = json_decode($rules, true);
        }
        if (!is_array($rules)) {
            $rules = [];
        }
        $descLower = strtolower($descClean);

        $unit = 'units';
        foreach ($rules as $keyword => $rule) {
            $keywordLower = strtolower(trim($keyword));
            $pattern = '/\b' . preg_quote($keywordLower, '/') . '\b/i';
            if (preg_match($pattern, $descLower)) {
                $unit = is_array($rule) ? ($rule['unit'] ?? 'units') : $rule;
                break;
            }
        }

        self::$itemUnitCache[$descClean] = $unit;
        return $unit;
    }

    /**
     * Get the request limit for a specific item.
     */
    public static function getItemRequestLimit($description, $category = null)
    {
        $descClean = trim($description);
        $cacheKey = $descClean . '_' . ($category ?? '');
        if (isset(self::$itemRequestLimitCache[$cacheKey])) {
            return self::$itemRequestLimitCache[$cacheKey];
        }

        $limits = self::get('item_request_limits', []);
        if (is_string($limits)) {
            $limits = json_decode($limits, true);
        }
        if (!is_array($limits)) {
            $limits = [];
        }
        $descLower = strtolower($descClean);

        $limit = null;
        foreach ($limits as $keyword => $rule) {
            $keywordLower = strtolower(trim($keyword));
            $pattern = '/\b' . preg_quote($keywordLower, '/') . '\b/i';
            if (preg_match($pattern, $descLower)) {
                $ruleCat = $rule['category'] ?? null;
                if ($ruleCat && $category && strcasecmp(trim($ruleCat), trim($category)) !== 0) {
                    continue;
                }
                $limit = (int)($rule['limit'] ?? $rule);
                break;
            }
        }

        self::$itemRequestLimitCache[$cacheKey] = $limit;
        return $limit;
    }

    protected static $requestRequisitionItems = null;

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
        if (is_string($limits)) {
            $limits = json_decode($limits, true);
        }
        if (!is_array($limits)) {
            $limits = [];
        }
        $descLower = strtolower(trim($description));
        $matchedKeyword = null;
        foreach ($limits as $keyword => $rule) {
            $keywordLower = strtolower(trim($keyword));
            $pattern = '/\b' . preg_quote($keywordLower, '/') . '\b/i';
            if (preg_match($pattern, $descLower)) {
                $ruleCat = $rule['category'] ?? null;
                if ($ruleCat && $category && strcasecmp(trim($ruleCat), trim($category)) !== 0) {
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

        // Memoize the query for the duration of the current HTTP request to prevent N+1 queries in loops
        if (self::$requestRequisitionItems === null) {
            self::$requestRequisitionItems = \App\Models\StoreRequisitionItem::join('store_requisitions', 'store_requisition_items.requisition_id', '=', 'store_requisitions.id')
                ->whereIn('store_requisitions.status', ['approved', 'partially_approved'])
                ->select(
                    'store_requisition_items.description',
                    'store_requisition_items.quantity_approved',
                    'store_requisition_items.alternative_description',
                    'store_requisition_items.alternative_quantity_approved'
                )
                ->get();
        }
        $items = self::$requestRequisitionItems;

        $pattern = '/\b' . preg_quote($keywordLower, '/') . '\b/i';
        $originalSum = 0.0;
        $alternativeSum = 0.0;

        foreach ($items as $dbItem) {
            if ($dbItem->description && preg_match($pattern, $dbItem->description)) {
                $originalSum += (float) $dbItem->quantity_approved;
            }
            if ($dbItem->alternative_description && preg_match($pattern, $dbItem->alternative_description)) {
                $alternativeSum += (float) $dbItem->alternative_quantity_approved;
            }
        }

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

