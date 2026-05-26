<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'avatar',
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
            'must_change_password'   => 'boolean',
            'is_temp_account'        => 'boolean',
        ];
    }

    /**
     * The Department Head who created this temporary account.
     */
    public function sponsor()
    {
        return $this->belongsTo(User::class, 'sponsored_by');
    }

    public function getSecurityStatus(): array
    {
        return ['label' => 'Verified', 'color' => '#10b981'];
    }
}
