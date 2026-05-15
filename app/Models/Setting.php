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
}
