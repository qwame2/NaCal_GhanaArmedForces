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
    protected static $unitConversionCache = [];

    public static function clearInventoryCache()
    {
        self::$itemThresholdCache = [];
        self::$itemUnitCache = [];
        self::$itemRequestLimitCache = [];
        self::$unitConversionCache = [];
        \Illuminate\Support\Facades\Cache::forget('dashboard_metrics_data');
        \Illuminate\Support\Facades\Cache::forget('low_stock_items_list');
        \Illuminate\Support\Facades\Cache::forget('item_aggregates_list');
        \Illuminate\Support\Facades\Cache::forget('global_low_stock_alerts');
        \Illuminate\Support\Facades\Cache::forget('global_expired_alerts');
        \Illuminate\Support\Facades\Cache::forget('temporary_returns_stats');
        \Illuminate\Support\Facades\Cache::forget('admin_requisitions_stats');
        \Illuminate\Support\Facades\Cache::forget('admin_requisitions_chart_data');
    }

    protected static function booted()
    {
        static::saved(function ($setting) {
            self::$cachedSettings = [];
            self::$itemThresholdCache = [];
            self::$itemUnitCache = [];
            self::$itemRequestLimitCache = [];
            self::$unitConversionCache = [];
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
            self::$unitConversionCache = [];
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
                            'contact_person' => $supplier->contact_person,
                            'contact_phone' => $supplier->contact_phone,
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

        $bypassCache = in_array($key, ['delegation_otp_code', 'delegation_otp_expires_at', 'delegated_approver_id']);

        if (!$bypassCache && array_key_exists($key, self::$cachedSettings)) {
            return self::$cachedSettings[$key];
        }

        if ($bypassCache) {
            $setting = self::where('key', $key)->first();
            if (!$setting) {
                return $default;
            }
            $val = $setting->value;
            if ($setting->type === 'integer') {
                $val = (int) $setting->value;
            }
            return $val;
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
            if (is_string($val) && (in_array($key, ['stores_dept_head_approval_categories', 'dg_approval_categories', 'dg_approval_items', 'inventory_categories']) || str_starts_with(trim($val), '['))) {
                $decoded = json_decode($val, true);
                if (is_array($decoded)) {
                    $val = $decoded;
                }
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
     * Get the code for a category name or code.
     */
    public static function getCategoryCode($name)
    {
        if (empty($name)) {
            return null;
        }
        $categories = self::getCategories();
        $nameTrimmed = strtolower(trim($name));
        foreach ($categories as $code => $catName) {
            if (strtolower(trim($code)) === $nameTrimmed || strtolower(trim($catName)) === $nameTrimmed) {
                return strtoupper($code);
            }
        }
        return null;
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
            if (self::isItemKeywordMatch($keyword, $descClean)) {
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

        $unit = 'units';
        foreach ($rules as $keyword => $rule) {
            if (self::isItemKeywordMatch($keyword, $descClean)) {
                $unit = is_array($rule) ? ($rule['unit'] ?? 'units') : $rule;
                break;
            }
        }

        self::$itemUnitCache[$descClean] = $unit;
        return $unit;
    }

    public static function normalizeItemKey($str)
    {
        return strtolower(preg_replace('/[^a-z0-9]/i', '', $str ?? ''));
    }

    public static function isItemKeywordMatch($keyword, $description)
    {
        $kwClean = strtolower(trim((string)$keyword));
        $descClean = strtolower(trim((string)$description));
        if ($kwClean === '' || $descClean === '') return false;

        // 1. Exact or word boundary match
        $pattern = '/\b' . preg_quote($kwClean, '/') . '\b/i';
        if (preg_match($pattern, $descClean)) return true;

        // 2. Substring match
        if (str_contains($descClean, $kwClean) || str_contains($kwClean, $descClean)) return true;

        // 3. Normalized alphanumeric match (ignores spaces, hyphens, punctuation)
        $normKw = self::normalizeItemKey($kwClean);
        $normDesc = self::normalizeItemKey($descClean);
        if ($normKw !== '' && $normDesc !== '') {
            if ($normKw === $normDesc || str_contains($normDesc, $normKw) || str_contains($normKw, $normDesc)) {
                return true;
            }
            // 4. Fuzzy distance match for typos (e.g. A4 SHHET vs A4 SHEET)
            if (strlen($normKw) >= 4 && strlen($normDesc) >= 4) {
                $lev = levenshtein($normKw, $normDesc);
                if ($lev <= 2) return true;
            }
        }
        return false;
    }

    public static function isExactOrTypoMatch($desc1, $desc2)
    {
        $clean1 = strtolower(trim((string)$desc1));
        $clean2 = strtolower(trim((string)$desc2));
        if ($clean1 === '' || $clean2 === '') return false;
        if ($clean1 === $clean2) return true;

        $norm1 = self::normalizeItemKey($desc1);
        $norm2 = self::normalizeItemKey($desc2);
        if ($norm1 === '' || $norm2 === '') return false;
        if ($norm1 === $norm2) return true;

        // Only merge if string lengths are nearly identical (<=2 char diff) and Levenshtein <= 2 (typos like A4 SHEET vs A4 SHHET)
        if (abs(strlen($norm1) - strlen($norm2)) <= 2 && strlen($norm1) >= 4 && strlen($norm2) >= 4) {
            if (levenshtein($norm1, $norm2) <= 2) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get unit conversion rule for a specific item.
     */
    public static function getUnitConversionRule($description, $category = null)
    {
        if (empty($description)) {
            return null;
        }
        $descClean = trim($description);
        $cacheKey = strtolower($descClean) . '_' . ($category ?? '');
        if (array_key_exists($cacheKey, self::$unitConversionCache)) {
            return self::$unitConversionCache[$cacheKey];
        }

        $rules = self::get('unit_conversion_rules', []);
        if (is_string($rules)) {
            $rules = json_decode($rules, true);
        }
        if (!is_array($rules)) {
            $rules = [];
        }

        $matchedRule = null;
        foreach ($rules as $keyword => $rule) {
            if (self::isItemKeywordMatch($keyword, $descClean)) {
                $ruleCat = is_array($rule) ? ($rule['category'] ?? null) : null;
                if ($ruleCat && $category && strcasecmp(trim($ruleCat), trim($category)) !== 0) {
                    continue;
                }
                $recUnit = is_array($rule) ? ($rule['received_unit'] ?? null) : null;
                if (empty($recUnit)) {
                    $recUnit = \App\Models\InventoryItem::whereRaw('TRIM(description) = ?', [$descClean])->value('unit') ?: 'Boxes';
                }

                $matchedRule = [
                    'keyword' => $keyword,
                    'category' => is_array($rule) ? ($rule['category'] ?? null) : null,
                    'received_unit' => $recUnit,
                    'requisition_unit' => is_array($rule) ? ($rule['requisition_unit'] ?? 'Reams') : 'Reams',
                    'conversion_factor' => (float)(is_array($rule) ? ($rule['conversion_factor'] ?? 1) : 1),
                ];
                break;
            }
        }

        self::$unitConversionCache[$cacheKey] = $matchedRule;
        return $matchedRule;
    }

    public static function formatUnitLabel($unit, $qty)
    {
        $unit = trim((string)$unit);
        if ($unit === '') {
            return '';
        }
        $qty = (float)$qty;
        if ($qty == 1) {
            return $unit;
        }
        if (preg_match('/\(s\)$/i', $unit) || preg_match('/\(es\)$/i', $unit) || preg_match('/s$/i', $unit) || preg_match('/^[A-Z]{2,4}$/', $unit)) {
            return $unit;
        }
        if (preg_match('/(ch|sh|x|z|s)$/i', $unit)) {
            return $unit . 'es';
        }
        if (preg_match('/y$/i', $unit) && !preg_match('/[aeiou]y$/i', $unit)) {
            return substr($unit, 0, -1) . 'ies';
        }
        return $unit . 's';
    }

    /**
     * Format stock balance with unit conversion rule breakdown if applicable.
     * Example: 9.4 Boxes with conversion factor 5 (Reams/Box) -> "9 Boxes and 2 Reams remaining (equivalent to 47 Reams)"
     */
    public static function formatStockBalanceWithConversion($stockBalance, $description, $category = null, $defaultStorageUnit = 'units', $showEquivalent = true)
    {
        $balanceNum = floatval(str_replace(',', '', $stockBalance ?? 0));
        $rule = self::getUnitConversionRule($description, $category);

        if (!$rule || empty($rule['conversion_factor']) || $rule['conversion_factor'] <= 1) {
            $unitStr = self::formatUnitLabel($defaultStorageUnit ?? 'units', $balanceNum);
            $formattedNum = (floor($balanceNum) == $balanceNum) ? number_format($balanceNum, 0) : number_format($balanceNum, 2);
            return "{$formattedNum} {$unitStr}";
        }

        $storageUnit = !empty($rule['received_unit']) ? $rule['received_unit'] : ($defaultStorageUnit ?? 'Boxes');
        $reqUnit = !empty($rule['requisition_unit']) ? $rule['requisition_unit'] : 'Reams';
        $factor = (float)$rule['conversion_factor'];

        // Total available in requisition units (e.g. 9.4 Boxes * 5 = 47 Reams)
        $totalReqUnits = round($balanceNum * $factor, 4);

        if ($totalReqUnits <= 0) {
            $zeroUnit = self::formatUnitLabel($storageUnit, 0);
            return "0 {$zeroUnit}";
        }

        $fullStorageUnits = (int) floor($totalReqUnits / $factor);
        $leftoverReqUnits = round(fmod($totalReqUnits, $factor), 2);
        if (floor($leftoverReqUnits) == $leftoverReqUnits) {
            $leftoverReqUnits = (int)$leftoverReqUnits;
        }

        $storageLabel = self::formatUnitLabel($storageUnit, $fullStorageUnits);
        $reqLabelTotal = self::formatUnitLabel($reqUnit, $totalReqUnits);
        $reqLabelLeftover = self::formatUnitLabel($reqUnit, $leftoverReqUnits);

        $formattedReqVal = (floor($totalReqUnits) == $totalReqUnits ? number_format($totalReqUnits, 0) : number_format($totalReqUnits, 2));
        $equivalentText = $showEquivalent ? "<br><span style=\"font-size: 0.78rem; font-weight: 700; color: #0284c7;\">({$formattedReqVal} {$reqLabelTotal})</span>" : "";

        if ($leftoverReqUnits > 0) {
            if ($fullStorageUnits > 0) {
                return "{$fullStorageUnits} {$storageLabel} and {$leftoverReqUnits} {$reqLabelLeftover}{$equivalentText}";
            } else {
                return "{$leftoverReqUnits} {$reqLabelLeftover}{$equivalentText}";
            }
        } else {
            $fullNum = number_format($fullStorageUnits, 0);
            return "{$fullNum} {$storageLabel}{$equivalentText}";
        }
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

