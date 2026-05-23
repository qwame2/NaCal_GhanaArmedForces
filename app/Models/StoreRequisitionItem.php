<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreRequisitionItem extends Model
{
    protected $fillable = [
        'requisition_id',
        'description',
        'category',
        'unit',
        'quantity_requested',
        'quantity_approved',
        'remarks',
        'alternative_description',
        'alternative_quantity_approved',
    ];

    public function requisition()
    {
        return $this->belongsTo(StoreRequisition::class, 'requisition_id');
    }
}
