<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'user_id', 'role_id', 'branch_id', 'employee_code', 'name', 'phone', 'email', 'address',
        'joining_date', 'shift', 'status', 'pos_pin_hash', 'permission_overrides',
    ];

    protected $casts = [
        'joining_date' => 'date',
        'permission_overrides' => 'array',
    ];

    protected $hidden = ['pos_pin_hash'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /** Role defaults merged with this employee's own overrides — mirrors the frontend's effectivePermissions(). */
    public function orders()
    {
        return $this->hasMany(Order::class, 'waiter_id');
    }

    public function effectivePermissions(): array
    {
        $base = [];
        foreach ($this->role?->permissions ?? [] as $permission) {
            $base[$permission->module][] = $permission->action;
        }

        foreach ($this->permission_overrides ?? [] as $module => $actions) {
            $base[$module] = $actions;
        }

        return $base;
    }
}
