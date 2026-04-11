<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'ledge_category',
        'supplier_name',
        'entry_date'
    ];

    public function items()
    {
        return $this->hasMany(InventoryItem::class, 'batch_id');
    }
}
