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
        'donor_name',
        'acquisition_type',
        'entry_date',
        'arrival_date'
    ];

    public function items()
    {
        return $this->hasMany(InventoryItem::class, 'batch_id');
    }
}
