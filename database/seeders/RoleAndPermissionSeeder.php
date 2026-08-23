<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleAndPermissionSeeder extends Seeder
{
    /** Mirrors resources/js/employees/demo-data.js exactly (MODULES × ACTIONS, ROLE_DEFAULTS). */
    public function run(): void
    {
        $modules = ['POS', 'Orders', 'Kitchen', 'Billing', 'Customers', 'Menu', 'Inventory', 'Purchases', 'Expenses', 'Reports', 'Employees', 'Settings'];
        $actions = ['View', 'Create', 'Edit', 'Cancel', 'Approve', 'Refund', 'Export'];

        $permissions = [];
        foreach ($modules as $module) {
            foreach ($actions as $action) {
                $permissions[$module][$action] = Permission::firstOrCreate(['module' => $module, 'action' => $action])->id;
            }
        }

        $roleDefaults = [
            'Restaurant Owner' => array_fill_keys($modules, $actions),
            'Restaurant Manager' => array_fill_keys($modules, $actions),
            'Cashier' => [
                'POS' => ['View', 'Create'], 'Orders' => ['View'],
                'Billing' => ['View', 'Create', 'Cancel'], 'Customers' => ['View', 'Create'], 'Reports' => ['View'],
            ],
            'Waiter' => [
                'POS' => ['View', 'Create'], 'Orders' => ['View', 'Create'], 'Customers' => ['View', 'Create'],
            ],
            'Kitchen Manager' => [
                'Kitchen' => ['View', 'Edit', 'Cancel'], 'Orders' => ['View'], 'Inventory' => ['View', 'Edit'],
            ],
            'Chef' => [
                'Kitchen' => ['View', 'Edit'],
            ],
            'Inventory Manager' => [
                'Inventory' => ['View', 'Create', 'Edit', 'Approve'], 'Purchases' => ['View', 'Create', 'Edit', 'Approve'], 'Reports' => ['View'],
            ],
        ];

        foreach ($roleDefaults as $roleName => $modulePerms) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $ids = [];
            foreach ($modulePerms as $module => $moduleActions) {
                foreach ($moduleActions as $action) {
                    if (isset($permissions[$module][$action])) {
                        $ids[] = $permissions[$module][$action];
                    }
                }
            }
            $role->permissions()->sync($ids);
        }
    }
}
