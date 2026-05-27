<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StoreRequisition extends Model
{
    use HasFactory;

    protected $appends = ['unique_id'];

    public function getUniqueIdAttribute(): string
    {
        return 'REQ-' . str_pad($this->id, 5, '0', STR_PAD_LEFT);
    }

    protected $fillable = [
        'requester_name',
        'department',
        'rank_or_title',
        'requested_by',
        'purpose',
        'priority',
        'status',
        'admin_notes',
        'decline_reason',
        'processed_by',
        'processed_at',
        'collected_at',
        'collected_by',
        'usage_type',
        'collector_name',
        'collector_contact',
        'collector_location',
        'origin_admin_status',
        'origin_approved_by',
        'alternative_status',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
        'collected_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(StoreRequisitionItem::class, 'requisition_id');
    }

    public function receipt()
    {
        return $this->hasOne(Receipt::class, 'requisition_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function collector()
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    public function getPriorityBadgeAttribute(): array
    {
        return match($this->priority) {
            'urgent' => ['label' => 'URGENT', 'color' => '#dc2626', 'bg' => 'rgba(220,38,38,0.1)'],
            'low'    => ['label' => 'LOW',    'color' => '#64748b', 'bg' => 'rgba(100,116,139,0.1)'],
            default  => ['label' => 'NORMAL', 'color' => '#4f46e5', 'bg' => 'rgba(79,70,229,0.1)'],
        };
    }

    public function getStatusBadgeAttribute(): array
    {
        if ($this->status === 'pending') {
            if ($this->alternative_status === 'proposed') {
                return ['label' => 'Alt Proposed - Awaiting Dept response', 'color' => '#ea580c', 'bg' => 'rgba(234,88,12,0.1)'];
            }
            if ($this->alternative_status === 'agreed') {
                return ['label' => 'Alt Agreed - Ready to Commit', 'color' => '#10b981', 'bg' => 'rgba(16,185,129,0.1)'];
            }
            if (($this->origin_admin_status ?? 'pending') === 'pending') {
                return ['label' => 'Awaiting Dept Head Approval', 'color' => '#6366f1', 'bg' => 'rgba(99,102,241,0.1)'];
            }
            if (($this->main_admin_status ?? 'pending') === 'pending') {
                return ['label' => 'Awaiting Store Head Review', 'color' => '#f59e0b', 'bg' => 'rgba(245,158,11,0.1)'];
            }
            return ['label' => 'Awaiting Admin Review', 'color' => '#6366f1', 'bg' => 'rgba(99,102,241,0.1)'];
        }

        return match($this->status) {
            'approved'           => ['label' => 'Approved',           'color' => '#10b981', 'bg' => 'rgba(16,185,129,0.1)'],
            'partially_approved' => ['label' => 'Partial Approval',   'color' => '#f59e0b', 'bg' => 'rgba(245,158,11,0.1)'],
            'declined'           => ['label' => 'Declined',           'color' => '#ef4444', 'bg' => 'rgba(239,68,68,0.1)'],
            default              => ['label' => 'Pending Review',     'color' => '#6366f1', 'bg' => 'rgba(99,102,241,0.1)'],
        };
    }

    public function getUsageTypeBadgeAttribute(): array
    {
        return match($this->usage_type) {
            'temporary' => ['label' => 'Temporary', 'color' => '#ea580c', 'bg' => 'rgba(234,88,12,0.1)'],
            default     => ['label' => 'Permanent', 'color' => '#10b981', 'bg' => 'rgba(16,185,129,0.1)'],
        };
    }
}
