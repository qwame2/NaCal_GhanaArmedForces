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
        'supplier_status',
        'entry_date',
        'arrival_date',
        'recorded_by',
        'approval_status',
        'approved_by',
        'approved_at'
    ];

    public function items()
    {
        return $this->hasMany(InventoryItem::class, 'batch_id');
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
