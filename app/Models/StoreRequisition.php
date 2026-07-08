<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StoreRequisition extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::saved(function () {
            Setting::clearInventoryCache();
        });
        static::deleted(function () {
            Setting::clearInventoryCache();
        });
    }

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
        'collector_staff_id',
        'origin_admin_status',
        'origin_approved_by',
        'main_admin_status',
        'stores_approved_by',
        'alternative_status',
        'requires_dg_approval',
        'dg_status',
        'dg_approved_by',
        'dg_approved_at',
        'dg_decline_reason',
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
                return ['label' => 'Awaiting Authorizer Review', 'color' => '#f59e0b', 'bg' => 'rgba(245,158,11,0.1)'];
            }
            if ($this->requires_dg_approval && ($this->dg_status ?? 'pending') === 'pending') {
                return ['label' => 'Awaiting DG Approval', 'color' => '#8b5cf6', 'bg' => 'rgba(139,92,246,0.1)'];
            }
            return ['label' => 'Awaiting Admin Review', 'color' => '#ef4444', 'bg' => 'rgba(239,68,68,0.1)'];
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

    protected static $deptHeadsCache = null;

    public function getApproverNameAttribute(): string
    {
        if (($this->origin_admin_status ?? 'pending') === 'pending') {
            if (self::$deptHeadsCache === null) {
                self::$deptHeadsCache = \App\Models\User::where('role', 'Department Head')
                    ->where('is_active', true)
                    ->get()
                    ->keyBy(fn($u) => strtolower(trim($u->department)));
            }
            $key = strtolower(trim($this->department));
            $hod = self::$deptHeadsCache->get($key);
            return $hod ? $hod->name . ' (HOD)' : 'Department Head';
        }
        if (($this->main_admin_status ?? 'pending') === 'pending') {
            return 'Authorizer';
        }
        if ($this->requires_dg_approval && ($this->dg_status ?? 'pending') === 'pending') {
            return 'Director General';
        }
        return 'N/A';
    }

    public function getTrackingPipelineAttribute(): array
    {
        $steps = [];
        $hodStatus = $this->origin_admin_status;
        $finalStatus = $this->status;

        // Step 1: HOD Review
        if ($hodStatus === 'declined' || ($hodStatus === 'pending' && $finalStatus === 'declined')) {
            $steps['hod'] = ['label' => 'HOD Declined', 'status' => 'declined', 'icon' => 'x', 'user' => $this->origin_approved_by ?? 'Department Head'];
        } elseif ($hodStatus === 'approved') {
            $steps['hod'] = ['label' => 'HOD Approved', 'status' => 'completed', 'icon' => 'check', 'user' => $this->origin_approved_by];
        } else {
            $hodName = 'Department Head';
            $key = strtolower(trim($this->department));
            if (self::$deptHeadsCache === null) {
                self::$deptHeadsCache = \App\Models\User::where('role', 'Department Head')
                    ->where('is_active', true)
                    ->get()
                    ->keyBy(fn($u) => strtolower(trim($u->department)));
            }
            $hod = self::$deptHeadsCache->get($key);
            if ($hod) {
                $hodName = $hod->name;
            }
            $steps['hod'] = ['label' => 'Awaiting HOD Review', 'status' => 'active', 'icon' => 'clock', 'user' => $hodName];
        }

        // Step 2: Stores HOD Review
        $storesApprovalCategories = \App\Models\Setting::get('stores_dept_head_approval_categories', []);
        if (is_string($storesApprovalCategories)) {
            $storesApprovalCategories = json_decode($storesApprovalCategories, true) ?? [];
        }
        $requiresStoresDeptHeadApproval = false;
        foreach ($this->items as $item) {
            if (in_array($item->category, $storesApprovalCategories)) {
                $requiresStoresDeptHeadApproval = true;
                break;
            }
        }

        if (!$requiresStoresDeptHeadApproval) {
            $steps['stores_hod'] = ['label' => 'Stores HOD Bypassed', 'status' => 'bypassed', 'icon' => 'minus', 'user' => 'N/A'];
        } else {
            $storesStatus = $this->main_admin_status;
            if ($hodStatus === 'declined' || ($hodStatus === 'pending' && $finalStatus === 'declined')) {
                $steps['stores_hod'] = ['label' => 'Stores HOD Bypassed', 'status' => 'bypassed', 'icon' => 'minus', 'user' => 'N/A'];
            } elseif ($storesStatus === 'declined' || ($storesStatus === 'pending' && $finalStatus === 'declined')) {
                $steps['stores_hod'] = ['label' => 'Stores HOD Declined', 'status' => 'declined', 'icon' => 'x', 'user' => $this->stores_approved_by ?? 'Stores Department Head'];
            } elseif ($storesStatus === 'approved') {
                $steps['stores_hod'] = ['label' => 'Stores HOD Approved', 'status' => 'completed', 'icon' => 'check', 'user' => $this->stores_approved_by];
            } elseif ($hodStatus === 'approved') {
                $steps['stores_hod'] = ['label' => 'Awaiting Stores HOD Review', 'status' => 'active', 'icon' => 'clock', 'user' => 'Stores Department Head'];
            } else {
                $steps['stores_hod'] = ['label' => 'Pending Stores HOD Review', 'status' => 'pending', 'icon' => 'circle', 'user' => 'Stores Department Head'];
            }
        }

        // Step 3: DG Approval
        if (!$this->requires_dg_approval) {
            $steps['dg'] = ['label' => 'DG Bypassed', 'status' => 'bypassed', 'icon' => 'minus', 'user' => 'N/A'];
        } else {
            $dgStatus = $this->dg_status;
            if ($hodStatus === 'declined' || $this->main_admin_status === 'declined' || $finalStatus === 'declined') {
                $steps['dg'] = ['label' => 'DG Bypassed', 'status' => 'bypassed', 'icon' => 'minus', 'user' => 'N/A'];
            } elseif ($dgStatus === 'declined') {
                $steps['dg'] = ['label' => 'DG Declined', 'status' => 'declined', 'icon' => 'x', 'user' => 'Director General'];
            } elseif ($dgStatus === 'approved') {
                $steps['dg'] = ['label' => 'DG Approved', 'status' => 'completed', 'icon' => 'check', 'user' => $this->dg_approved_by ?? 'Director General'];
            } elseif ($hodStatus === 'approved' && ($this->main_admin_status === 'approved' || !$requiresStoresDeptHeadApproval)) {
                $steps['dg'] = ['label' => 'Awaiting DG Approval', 'status' => 'active', 'icon' => 'clock', 'user' => 'Director General'];
            } else {
                $steps['dg'] = ['label' => 'Pending DG Approval', 'status' => 'pending', 'icon' => 'circle', 'user' => 'Director General'];
            }
        }

        // Step 4: Head of Stores Final Review
        if ($hodStatus === 'declined' || $this->main_admin_status === 'declined' || ($this->requires_dg_approval && $this->dg_status === 'declined') || $finalStatus === 'declined') {
            $steps['head_of_stores'] = ['label' => 'No Review (Declined)', 'status' => 'bypassed', 'icon' => 'minus', 'user' => 'N/A'];
        } elseif (in_array($finalStatus, ['approved', 'partially_approved'])) {
            $steps['head_of_stores'] = ['label' => 'Issued / Completed', 'status' => 'completed', 'icon' => 'check', 'user' => $this->processor?->name ?? 'Head of Stores'];
        } elseif ($hodStatus === 'approved' && ($this->main_admin_status === 'approved' || !$requiresStoresDeptHeadApproval) && (!$this->requires_dg_approval || $this->dg_status === 'approved')) {
            $steps['head_of_stores'] = ['label' => 'Awaiting Head of Stores Review', 'status' => 'active', 'icon' => 'clock', 'user' => 'Head of Stores'];
        } else {
            $steps['head_of_stores'] = ['label' => 'Pending Head of Stores Review', 'status' => 'pending', 'icon' => 'circle', 'user' => 'Head of Stores'];
        }

        return $steps;
    }
}

