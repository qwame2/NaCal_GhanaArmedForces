<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRoleHistory extends Model
{
    protected $fillable = [
        'user_id',
        'changed_by',
        'action',
        'old_role',
        'new_role',
        'old_is_admin',
        'new_is_admin',
        'old_permissions',
        'new_permissions',
    ];

    protected $casts = [
        'old_permissions' => 'array',
        'new_permissions' => 'array',
        'old_is_admin' => 'boolean',
        'new_is_admin' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function changer()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
