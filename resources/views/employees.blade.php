<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Employees · Royal Bengal Restaurant</title>
    @vite(['resources/css/app.css', 'resources/css/pos.css', 'resources/css/admin.css', 'resources/js/employees.js'])
    <script>
        window.employeeModule = @json($employeeModule);
        window.employeeRoutes = {
            data: @json(route('employees.data')),
            store: @json(route('employees.store')),
            update: @json(url('/employees')),
        };
    </script>
</head>
<body x-data="employeesApp" x-cloak class="adm-root adm-shell bg-slate-100 text-slate-900 antialiased">

    <x-shell.sidebar active="employees" />

    <div class="adm-main">
        <x-admin.page-header title="Employees" subtitle="Ichapur Main Branch">
            <button type="button" @click="openCreate()" class="flex h-8 items-center gap-1.5 rounded-md bg-slate-900 px-3 text-[11.5px] font-black uppercase tracking-wide text-white hover:bg-slate-800">
                <x-pos.icon name="plus" class="h-3.5 w-3.5" stroke="2.4" /> Add Employee
            </button>
        </x-admin.page-header>

        <div class="pos-infobar flex items-center gap-1.5 overflow-x-auto border-b border-slate-200 bg-white px-4 pos-no-scrollbar">
            <div class="flex h-8 items-center gap-1.5 rounded-md px-2"><span class="text-[10.5px] font-bold uppercase tracking-wide text-slate-500">Total</span><span class="pos-num text-[13px] font-black text-slate-900" x-text="summary.total"></span></div>
            <div class="flex h-8 items-center gap-1.5 rounded-md px-2"><span class="text-[10.5px] font-bold uppercase tracking-wide text-emerald-600">Active</span><span class="pos-num text-[13px] font-black text-emerald-700" x-text="summary.active"></span></div>
            <div class="flex h-8 items-center gap-1.5 rounded-md px-2"><span class="text-[10.5px] font-bold uppercase tracking-wide text-slate-500">Waiters</span><span class="pos-num text-[13px] font-black text-slate-900" x-text="summary.waiters"></span></div>
        </div>

        <div class="pos-dock flex flex-wrap items-center gap-2 border-b border-slate-200 bg-white px-4 py-2">
            <div class="relative min-w-[190px] max-w-xs flex-1"><x-pos.icon name="search" class="pointer-events-none absolute left-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" /><input x-model="query" @input="page=1" placeholder="Search name / ID / phone…" class="h-8 w-full rounded-md border border-slate-300 bg-white pl-8 pr-2 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" /></div>
            <select x-model="roleFilter" @change="page=1" class="h-8 rounded-md border border-slate-300 bg-white px-2 text-[11.5px] font-bold text-slate-700 focus:border-slate-900 focus:outline-none"><option value="all">All Roles</option><template x-for="r in roles" :key="r"><option x-text="r"></option></template></select>
            <button type="button" x-show="query || roleFilter !== 'all'" @click="clearFilters()" class="flex h-8 items-center gap-1 rounded-md border border-slate-300 px-2 text-[11px] font-bold text-slate-600 hover:border-slate-900"><x-pos.icon name="refresh" class="h-3.5 w-3.5" /> Reset</button>
        </div>

        <div class="adm-table-wrap bg-white">
            <table class="adm-table">
                <thead><tr><th>Employee</th><th>Employee ID</th><th>Role</th><th>Phone</th><th>Shift</th><th>Active Tables</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    <template x-for="e in paged" :key="e.id">
                        <tr class="adm-row-clickable" @pointerdown="armProfileOpen()" @click="openProfile(e)">
                            <td><div class="flex items-center gap-2"><x-admin.avatar initials-expr="initials(e.name)" size="sm" /><span class="font-bold text-slate-900" x-text="e.name"></span></div></td>
                            <td class="pos-num text-slate-500" x-text="e.employeeId"></td>
                            <td class="text-slate-600" x-text="e.role"></td>
                            <td class="pos-num text-slate-500" x-text="e.phone"></td>
                            <td class="text-slate-600" x-text="shiftTypes[e.shift]?.label"></td>
                            <td class="pos-num text-slate-500" x-text="e.activeTables ?? '—'"></td>
                            <td><x-admin.badge expr="e.status" /></td>
                            <td @click.stop>
                                <x-admin.action-menu id-expr="e.id">
                                    <button type="button" @click="openProfile(e)" class="flex w-full items-center px-3 py-2 text-left text-[12px] font-semibold text-slate-700 hover:bg-slate-50">View Profile</button>
                                    <button type="button" @click="openEdit(e)" class="flex w-full items-center px-3 py-2 text-left text-[12px] font-semibold text-slate-700 hover:bg-slate-50">Edit</button>
                                    <button type="button" x-show="e.status !== 'suspended'" @click="setStatus(e, 'suspended')" class="flex w-full items-center px-3 py-2 text-left text-[12px] font-semibold text-rose-600 hover:bg-rose-50">Suspend</button>
                                    <button type="button" x-show="e.status !== 'active'" @click="setStatus(e, 'active')" class="flex w-full items-center px-3 py-2 text-left text-[12px] font-semibold text-slate-700 hover:bg-slate-50">Reactivate</button>
                                </x-admin.action-menu>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
            <x-admin.empty-state icon="users" title="No employees match this filter" x-show="!paged.length" />
        </div>
        <x-admin.pagination total="filtered.length" />
    </div>

    <x-admin.toast />
    <div x-ref="overlayRoot"><x-employees.overlays /></div>
</body>
</html>
