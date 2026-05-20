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
    ];

    public function requisition()
    {
        return $this->belongsTo(StoreRequisition::class, 'requisition_id');
    }
}
