<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $actions = ['View', 'Create', 'Edit', 'Cancel', 'Approve', 'Refund', 'Export'];
        $now = now();

        foreach ($actions as $action) {
            DB::table('permissions')->updateOrInsert(
                ['module' => 'Dashboard', 'action' => $action],
                ['created_at' => $now, 'updated_at' => $now]
            );
        }

        $dashboardPermissions = DB::table('permissions')
            ->where('module', 'Dashboard')
            ->pluck('id', 'action');

        $roleDefaults = [
            'Restaurant Owner' => $actions,
            'Restaurant Manager' => $actions,
            'Cashier' => ['View'],
            'Waiter' => ['View'],
            'Kitchen Manager' => ['View'],
            'Inventory Manager' => ['View'],
        ];

        foreach ($roleDefaults as $roleName => $roleActions) {
            $roleId = DB::table('roles')->where('name', $roleName)->value('id');

            if (! $roleId) {
                continue;
            }

            foreach ($roleActions as $action) {
                $permissionId = $dashboardPermissions[$action] ?? null;

                if (! $permissionId) {
                    continue;
                }

                DB::table('role_permission')->updateOrInsert([
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                ]);
            }
        }
    }

    public function down(): void
    {
        $permissionIds = DB::table('permissions')
            ->where('module', 'Dashboard')
            ->pluck('id');

        DB::table('role_permission')
            ->whereIn('permission_id', $permissionIds)
            ->delete();

        DB::table('permissions')
            ->where('module', 'Dashboard')
            ->delete();
    }
};
