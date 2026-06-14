<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockHistory extends Model
{
    protected $fillable = [
        'inventory_item_id',
        'user_id',
        'action',
        'old_description',
        'new_description',
        'old_unit',
        'new_unit',
        'old_qty',
        'new_qty',
        'old_stock_balance',
        'new_stock_balance',
        'old_variance',
        'new_variance',
    ];

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
