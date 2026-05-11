<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'group', 'description'];

    /**
     * Get a setting value by key.
     */
    public static function get($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        if (!$setting) {
            return $default;
        }

        switch ($setting->type) {
            case 'boolean':
                return filter_var($setting->value, FILTER_VALIDATE_BOOLEAN);
            case 'integer':
                return (int) $setting->value;
            case 'json':
                return json_decode($setting->value, true);
            default:
                return $setting->value;
        }
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
}
