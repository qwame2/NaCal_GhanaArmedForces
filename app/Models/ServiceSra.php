<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ServiceSra extends Model
{
    use HasFactory;

    protected $table = 'service_sras';

    protected $fillable = [
        'sra_number',
        'submitted_by',
        'dept',
        'station',
        'region',
        'date_of_delivery',
        'supplier_name',
        'vehicle_number',
        'ae_number',
        'lpo_number',
        'supplier_address',
        'delivery_type',
        'previous_sra_nos',
        'details',
        'admin_status',
        'admin_approved_by',
        'admin_approved_at',
        'admin_notes',
        'auditor_status',
        'auditor_approved_by',
        'auditor_approved_at',
        'auditor_notes',
        'stores_status',
        'stores_approved_by',
        'stores_approved_at',
        'stores_notes',
        'status',
    ];

    protected $casts = [
        'date_of_delivery'   => 'date',
        'admin_approved_at'  => 'datetime',
        'auditor_approved_at'=> 'datetime',
        'stores_approved_at' => 'datetime',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

    public function getUniqueIdAttribute(): string
    {
        return 'SRA-' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'approved'        => ['label' => 'Fully Approved',              'color' => '#10b981', 'bg' => 'rgba(16,185,129,0.1)'],
            'admin_approved'  => ['label' => 'Awaiting Stores Review',     'color' => '#f59e0b', 'bg' => 'rgba(245,158,11,0.1)'],
            'auditor_pending' => ['label' => 'Awaiting Auditor Review',    'color' => '#8b5cf6', 'bg' => 'rgba(139,92,246,0.1)'],
            'declined'        => ['label' => 'Declined',                   'color' => '#ef4444', 'bg' => 'rgba(239,68,68,0.1)'],
            default           => ['label' => 'Awaiting Authorizer Review','color' => '#6366f1', 'bg' => 'rgba(99,102,241,0.1)'],
        };
     }
 
     public function getApprovalPipelineAttribute(): array
     {
         $steps = [];
 
         // Step 1 – Head of Admin
         if ($this->admin_status === 'approved') {
             $steps['admin'] = ['label' => 'Authorizer Approved', 'status' => 'completed', 'icon' => 'check', 'user' => $this->admin_approved_by];
         } elseif ($this->admin_status === 'declined') {
             $steps['admin'] = ['label' => 'Authorizer Declined', 'status' => 'declined',  'icon' => 'x',     'user' => $this->admin_approved_by];
         } else {
             $steps['admin'] = ['label' => 'Awaiting Authorizer Review', 'status' => 'active', 'icon' => 'clock', 'user' => 'Authorizer'];
         }

        // Step 2 – Auditor
        if ($this->admin_status !== 'approved') {
            $steps['auditor'] = ['label' => 'Pending Auditor Review', 'status' => 'pending', 'icon' => 'circle', 'user' => 'Auditor'];
        } elseif ($this->auditor_status === 'approved') {
            $steps['auditor'] = ['label' => 'Auditor Approved',  'status' => 'completed', 'icon' => 'check', 'user' => $this->auditor_approved_by];
        } elseif ($this->auditor_status === 'declined') {
            $steps['auditor'] = ['label' => 'Auditor Declined',  'status' => 'declined',  'icon' => 'x',     'user' => $this->auditor_approved_by];
        } else {
            $steps['auditor'] = ['label' => 'Awaiting Auditor Review', 'status' => 'active', 'icon' => 'clock', 'user' => 'Auditor'];
        }

        // Step 3 – Head of Stores
        if ($this->auditor_status !== 'approved') {
            $steps['stores'] = ['label' => 'Pending Stores Review', 'status' => 'pending', 'icon' => 'circle', 'user' => 'Head of Stores'];
        } elseif ($this->stores_status === 'approved') {
            $steps['stores'] = ['label' => 'Stores Approved',  'status' => 'completed', 'icon' => 'check', 'user' => $this->stores_approved_by];
        } elseif ($this->stores_status === 'declined') {
            $steps['stores'] = ['label' => 'Stores Declined',  'status' => 'declined',  'icon' => 'x',     'user' => $this->stores_approved_by];
        } else {
            $steps['stores'] = ['label' => 'Awaiting Stores Review', 'status' => 'active', 'icon' => 'clock', 'user' => 'Head of Stores'];
        }

        return $steps;
    }

    // ─── Auto-generate SRA number ─────────────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (self $sra) {
            if (empty($sra->sra_number)) {
                $max   = static::max('id') ?? 0;
                $sra->sra_number = 'SRA-' . str_pad($max + 1, 6, '0', STR_PAD_LEFT);
            }
        });
    }
}
