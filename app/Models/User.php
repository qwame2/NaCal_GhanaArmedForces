<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use LdapRecord\Laravel\Auth\AuthenticatesWithLdap;
use LdapRecord\Laravel\Auth\LdapAuthenticatable;

class User extends Authenticatable implements LdapAuthenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, AuthenticatesWithLdap;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'guid',
        'domain',
        'name',
        'username',
        'email',
        'password',
        'avatar',
        'signature',
        'role',
        'is_admin',
        'is_active',
        'is_online',
        'department',
        'phone',
        'service_number',
        'rank',
        'last_login_at',
        'recovery_secret',
        'must_change_password',
        'is_temp_account',
        'otp_token',
        'sponsored_by',
        'registration_status',
        'can_make_requisition',
        'can_approve_requisition',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at'      => 'datetime',
            'password'               => 'hashed',
            'last_login_at'          => 'datetime',
            'is_active'              => 'boolean',
            'is_online'              => 'boolean',
            'can_add_inventory'      => 'boolean',
            'can_operate_logistics'  => 'boolean',
            'can_generate_reports'   => 'boolean',
            'can_verify_stock'       => 'boolean',
            'can_make_requisition'   => 'boolean',
            'can_approve_requisition'=> 'boolean',
            'must_change_password'   => 'boolean',
            'is_temp_account'        => 'boolean',
        ];
    }

    public function getIsTempAccountAttribute($value)
    {
        return $this->role === 'Auditor' ? true : (bool)$value;
    }

    /**
     * The Department Head who created this temporary account.
     */
    public function sponsor()
    {
        return $this->belongsTo(User::class, 'sponsored_by');
    }

    public function getIsActiveAttribute($value)
    {
        if ($this->is_temp_account) {
            if (\App\Http\Controllers\TempRequisitionerController::hasOverdueReturn($this->department)) {
                return false;
            }
        }
        return (bool)$value;
    }

    public function getIsOnlineAttribute($value)
    {
        if (!$this->is_active) {
            return false;
        }
        return (bool)$value;
    }

    protected static function booted()
    {
        static::created(function ($user) {
            try {
                $perms = [
                    'can_add_inventory' => (bool)$user->can_add_inventory,
                    'can_operate_logistics' => (bool)$user->can_operate_logistics,
                    'can_generate_reports' => (bool)$user->can_generate_reports,
                    'can_verify_stock' => (bool)$user->can_verify_stock,
                    'can_make_requisition' => (bool)$user->can_make_requisition,
                    'can_approve_requisition' => (bool)$user->can_approve_requisition,
                ];
                \App\Models\UserRoleHistory::create([
                    'user_id' => $user->id,
                    'changed_by' => auth()->id(),
                    'action' => 'created',
                    'new_role' => $user->role,
                    'new_is_admin' => (bool)$user->is_admin,
                    'new_permissions' => $perms,
                ]);
            } catch (\Exception $e) {
                // Prevent failures
            }
        });

        static::updating(function ($user) {
            try {
                $monitored = [
                    'role', 'is_admin', 'is_active', 'registration_status',
                    'can_add_inventory', 'can_operate_logistics', 'can_generate_reports',
                    'can_verify_stock', 'can_make_requisition', 'can_approve_requisition'
                ];

                $changed = false;
                foreach ($monitored as $field) {
                    if ($user->isDirty($field)) {
                        $changed = true;
                        break;
                    }
                }

                if ($changed) {
                    $action = 'permissions_changed';
                    if ($user->isDirty('role')) {
                        $action = 'role_changed';
                    } elseif ($user->isDirty('is_active') || $user->isDirty('registration_status')) {
                        $action = 'status_changed';
                    }

                    $oldPerms = [
                        'can_add_inventory' => (bool)$user->getOriginal('can_add_inventory'),
                        'can_operate_logistics' => (bool)$user->getOriginal('can_operate_logistics'),
                        'can_generate_reports' => (bool)$user->getOriginal('can_generate_reports'),
                        'can_verify_stock' => (bool)$user->getOriginal('can_verify_stock'),
                        'can_make_requisition' => (bool)$user->getOriginal('can_make_requisition'),
                        'can_approve_requisition' => (bool)$user->getOriginal('can_approve_requisition'),
                    ];

                    $newPerms = [
                        'can_add_inventory' => (bool)$user->can_add_inventory,
                        'can_operate_logistics' => (bool)$user->can_operate_logistics,
                        'can_generate_reports' => (bool)$user->can_generate_reports,
                        'can_verify_stock' => (bool)$user->can_verify_stock,
                        'can_make_requisition' => (bool)$user->can_make_requisition,
                        'can_approve_requisition' => (bool)$user->can_approve_requisition,
                    ];

                    \App\Models\UserRoleHistory::create([
                        'user_id' => $user->id,
                        'changed_by' => auth()->id(),
                        'action' => $action,
                        'old_role' => $user->getOriginal('role'),
                        'new_role' => $user->role,
                        'old_is_admin' => $user->getOriginal('is_admin') === null ? null : (bool)$user->getOriginal('is_admin'),
                        'new_is_admin' => (bool)$user->is_admin,
                        'old_permissions' => $oldPerms,
                        'new_permissions' => $newPerms,
                    ]);
                }
            } catch (\Exception $e) {
                // Prevent failures
            }
        });
    }

    public function isDelegatedApprover()
    {
        if ($this->role !== 'Officer') {
            return false;
        }
        $delegatedId = \App\Models\Setting::get('delegated_approver_id');
        return $delegatedId && (int)$delegatedId === (int)$this->id;
    }

    /**
     * Returns true if this user is a Sub Main Admin (HR or Welfare backup approver).
     */
    public function isSubMainAdmin(): bool
    {
        return $this->role === 'Sub Main Admin';
    }

    /**
     * Returns true if this user is either the true Main Admin or a Sub Main Admin.
     */
    public function isMainAdminOrSub(): bool
    {
        return in_array($this->role, ['Main Admin', 'Sub Main Admin']);
    }

    /**
     * Returns the display number (1 or 2) for a Sub Main Admin based on last_login_at order.
     * The Sub Main Admin who logged in earliest is #1, the next is #2.
     * Returns null for non-Sub Main Admins.
     */
    public function getSubMainAdminNumber(): ?int
    {
        if (!$this->isSubMainAdmin()) return null;
        $ids = self::where('role', 'Sub Main Admin')
            ->orderBy('last_login_at', 'asc')
            ->orderBy('id', 'asc')
            ->pluck('id')
            ->values();
        $idx = $ids->search($this->id);
        return $idx !== false ? $idx + 1 : null;
    }

    /**
     * Returns the display label for this user's role.
     * Sub Main Admins get "Sub Main Admin 1" or "Sub Main Admin 2".
     */
    public function getRoleDisplayLabel(): string
    {
        if ($this->isSubMainAdmin()) {
            $num = $this->getSubMainAdminNumber();
            return 'Sub Main Admin' . ($num ? " {$num}" : '');
        }
        return $this->role ?? '';
    }

    public static function getApproversQuery()
    {
        $delegatedId = \App\Models\Setting::get('delegated_approver_id');
        return self::where(function($q) use ($delegatedId) {
            $q->where('is_admin', true);
            if ($delegatedId) {
                $q->orWhere('id', $delegatedId);
            }
        });
    }

    public static function isPrimaryStoresHeadOnline(): bool
    {
        // Dynamic clean up of stale admin statuses using the sessions table
        try {
            $activeSessionUserIds = \Illuminate\Support\Facades\DB::table('sessions')
                ->where('last_activity', '>=', now()->subSeconds(60)->timestamp)
                ->whereNotNull('user_id')
                ->pluck('user_id')
                ->toArray();

            self::where('is_admin', true)
                ->where('is_online', true)
                ->whereNotIn('id', $activeSessionUserIds)
                ->update(['is_online' => false]);
        } catch (\Exception $e) {}

        return self::where(function($q) {
                $q->whereIn('role', ['Main Admin', 'Sub Main Admin'])
                  ->orWhere('role', 'Dept. Head (Stores)')
                  ->orWhereIn('department', ['Stores', 'Store']);
            })
            ->where('is_online', true)
            ->where('is_active', true)
            ->exists();
    }

    public function getSecurityStatus(): array
    {
        return ['label' => 'Verified', 'color' => '#10b981'];
    }
}

