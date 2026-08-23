<?php

namespace Tests;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function actingAsEmployeeWithPermissions(array $permissions, string $roleName = 'Test Manager'): array
    {
        $user = User::factory()->create();
        $branch = Branch::firstOrCreate(['code' => 'ICH-01'], ['name' => 'Ichapur Main Branch']);
        $role = Role::firstOrCreate(['name' => $roleName]);

        $permissionIds = collect($permissions)->map(function (array $permission) {
            return Permission::firstOrCreate([
                'module' => $permission[0],
                'action' => $permission[1],
            ])->id;
        })->all();
        $role->permissions()->syncWithoutDetaching($permissionIds);

        $employee = Employee::create([
            'user_id' => $user->id,
            'role_id' => $role->id,
            'branch_id' => $branch->id,
            'employee_code' => 'EMP-' . str_pad((string) (Employee::count() + 1), 3, '0', STR_PAD_LEFT),
            'name' => $user->name,
            'phone' => '900000' . str_pad((string) $user->id, 4, '0', STR_PAD_LEFT),
            'status' => 'active',
        ]);

        $this->actingAs($user);

        return [$user, $employee, $role];
    }
}
