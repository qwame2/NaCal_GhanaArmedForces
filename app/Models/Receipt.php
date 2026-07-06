<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Receipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'requisition_id',
        'receipt_number',
        'collector_name',
        'collector_contact',
        'collector_location',
        'collector_staff_id',
        'collected_at',
        'issued_by',
        'approved_by_dept_head',
        'approved_by_stores_head',
        'items_json',
    ];

    protected $casts = [
        'collected_at' => 'datetime',
    ];

    public function requisition()
    {
        return $this->belongsTo(StoreRequisition::class, 'requisition_id');
    }

    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
