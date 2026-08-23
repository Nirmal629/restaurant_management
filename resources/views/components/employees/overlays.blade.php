{{-- Employee Profile — Profile / Permissions / Shifts / Activity / Performance tabs. --}}
<x-pos.dialog name="profile" variant="drawer" width="max-w-lg" title="Employee Profile" :subtitle="null">
    <template x-if="activeEmployee">
        <div>
            <div class="border-b border-slate-200 bg-white px-4 py-3">
                <div class="flex items-center gap-3">
                    <x-admin.avatar initials-expr="initials(activeEmployee.name)" size="lg" />
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-[14px] font-black text-slate-900" x-text="activeEmployee.name"></p>
                        <p class="pos-num text-[11px] font-semibold text-slate-500" x-text="activeEmployee.employeeId + ' · ' + activeEmployee.role"></p>
                    </div>
                    <x-admin.badge expr="activeEmployee.status" />
                </div>
            </div>
            <div class="flex items-center gap-1 overflow-x-auto border-b border-slate-200 bg-white px-3 py-2">
                @foreach (['profile' => 'Profile', 'permissions' => 'Permissions', 'shifts' => 'Shifts', 'activity' => 'Activity', 'performance' => 'Performance'] as $k => $l)
                    <button type="button" @click="activeTab = '{{ $k }}'" :class="activeTab === '{{ $k }}' ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-600'" class="shrink-0 rounded-md border px-2.5 py-1.5 text-[11px] font-bold uppercase tracking-wide">{{ $l }}</button>
                @endforeach
            </div>

            {{-- PROFILE --}}
            <div x-show="activeTab === 'profile'" class="p-3">
                <div class="grid grid-cols-2 gap-2 text-[11.5px]">
                    <p class="text-slate-500">Phone <span class="pos-num block font-bold text-slate-800" x-text="activeEmployee.phone"></span></p>
                    <p class="text-slate-500">Email <span class="block font-bold text-slate-800" x-text="activeEmployee.email || '—'"></span></p>
                    <p class="text-slate-500">Branch <span class="block font-bold text-slate-800" x-text="venue.branch"></span></p>
                    <p class="text-slate-500">Joining Date <span class="pos-num block font-bold text-slate-800" x-text="formatDate(activeEmployee.joiningDate)"></span></p>
                    <p class="col-span-2 text-slate-500">Address <span class="block font-bold text-slate-800" x-text="activeEmployee.address || '—'"></span></p>
                </div>
                <div class="mt-3 rounded-md border border-slate-200 bg-slate-50 p-3">
                    <p class="mb-1.5 text-[10px] font-black uppercase tracking-wide text-slate-400">Login Access</p>
                    <div class="grid grid-cols-2 gap-2 text-[11.5px]">
                        <p class="text-slate-500">Username <span class="block font-bold text-slate-800" x-text="(activeEmployee.email || activeEmployee.phone)"></span></p>
                        <p class="text-slate-500">POS PIN <span class="block font-bold text-slate-800" x-text="activeEmployee.pinSet ? '•••• (set)' : 'Not set'"></span></p>
                    </div>
                </div>
                <div class="mt-2 grid grid-cols-3 gap-1.5">
                    <button type="button" @click="setStatus(activeEmployee, 'active')" class="h-8 rounded-md border border-slate-300 bg-white text-[10.5px] font-bold text-slate-700 hover:border-emerald-500">Active</button>
                    <button type="button" @click="setStatus(activeEmployee, 'inactive')" class="h-8 rounded-md border border-slate-300 bg-white text-[10.5px] font-bold text-slate-700 hover:border-slate-500">Inactive</button>
                    <button type="button" @click="setStatus(activeEmployee, 'suspended')" class="h-8 rounded-md border border-rose-300 bg-white text-[10.5px] font-bold text-rose-600 hover:bg-rose-50">Suspend</button>
                </div>
            </div>

            {{-- PERMISSIONS --}}
            <div x-show="activeTab === 'permissions'" class="p-3">
                <p class="mb-2 text-[10.5px] text-slate-400">Role defaults shown; toggling a cell creates an employee-specific override.</p>
                <div class="adm-table-wrap" style="max-height: 50vh;">
                    <table class="adm-table">
                        <thead><tr><th>Module</th><template x-for="a in actions" :key="a"><th x-text="a"></th></template><th></th></tr></thead>
                        <tbody>
                            <template x-for="m in modules" :key="m">
                                <tr>
                                    <td class="font-semibold text-slate-800" x-text="m"></td>
                                    <template x-for="a in actions" :key="a">
                                        <td class="text-center">
                                            <button type="button" @click="togglePermission(activeEmployee, m, a)"
                                                    :class="hasPermission(activeEmployee, m, a) ? 'bg-slate-900 border-slate-900' : 'bg-white border-slate-300'"
                                                    class="mx-auto grid h-5 w-5 place-items-center rounded border">
                                                <x-pos.icon name="check" class="h-3 w-3 text-white" stroke="3" x-show="hasPermission(activeEmployee, m, a)" />
                                            </button>
                                        </td>
                                    </template>
                                    <td>
                                        <button type="button" x-show="isOverridden(activeEmployee, m)" @click="resetModuleToDefault(activeEmployee, m)" class="text-[9.5px] font-bold text-amber-600 underline decoration-amber-300 underline-offset-2">Reset</button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- SHIFTS --}}
            <div x-show="activeTab === 'shifts'" class="p-3">
                <div class="grid grid-cols-3 gap-1.5">
                    <template x-for="[key, s] in Object.entries(shiftTypes)" :key="key">
                        <button type="button" @click="setShift(activeEmployee, key)" :class="activeEmployee.shift === key ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-700'" class="rounded-md border p-3 text-center">
                            <span class="block text-[12px] font-bold" x-text="s.label"></span>
                            <span class="pos-num mt-0.5 block text-[10.5px] opacity-75" x-text="s.start + ' – ' + s.end"></span>
                        </button>
                    </template>
                </div>
            </div>

            {{-- ACTIVITY --}}
            <div x-show="activeTab === 'activity'" class="p-3">
                <div class="space-y-2 border-l-2 border-slate-200 pl-3">
                    <template x-for="(a, i) in activeEmployee.activity" :key="i">
                        <div><p class="text-[11.5px] font-semibold text-slate-700" x-text="a.text"></p><p class="text-[10px] font-medium text-slate-400" x-text="a.at"></p></div>
                    </template>
                </div>
                <x-admin.empty-state icon="clipboard" title="No activity logged" x-show="!activeEmployee.activity.length" />
            </div>

            {{-- PERFORMANCE --}}
            <div x-show="activeTab === 'performance'" class="p-3">
                <template x-if="activeEmployee.performance?.orders !== undefined">
                    <div class="grid grid-cols-2 gap-2">
                        <div class="rounded-md border border-slate-200 bg-slate-50 p-2.5"><p class="text-[9.5px] font-black uppercase text-slate-400">Orders</p><p class="pos-num text-[16px] font-black text-slate-900" x-text="activeEmployee.performance.orders"></p></div>
                        <div class="rounded-md border border-slate-200 bg-slate-50 p-2.5"><p class="text-[9.5px] font-black uppercase text-slate-400">Sales</p><p class="pos-num text-[16px] font-black text-slate-900" x-text="'₹' + activeEmployee.performance.sales.toLocaleString('en-IN')"></p></div>
                        <div class="rounded-md border border-slate-200 bg-slate-50 p-2.5"><p class="text-[9.5px] font-black uppercase text-slate-400">Average Bill</p><p class="pos-num text-[16px] font-black text-slate-900" x-text="'₹' + activeEmployee.performance.avgBill"></p></div>
                        <div class="rounded-md border border-slate-200 bg-slate-50 p-2.5"><p class="text-[9.5px] font-black uppercase text-slate-400">Tables Served</p><p class="pos-num text-[16px] font-black text-slate-900" x-text="activeEmployee.performance.tablesServed"></p></div>
                    </div>
                </template>
                <template x-if="activeEmployee.performance?.ordersPrepared !== undefined">
                    <div class="grid grid-cols-2 gap-2">
                        <div class="rounded-md border border-slate-200 bg-slate-50 p-2.5"><p class="text-[9.5px] font-black uppercase text-slate-400">Orders Prepared</p><p class="pos-num text-[16px] font-black text-slate-900" x-text="activeEmployee.performance.ordersPrepared"></p></div>
                        <div class="rounded-md border border-slate-200 bg-slate-50 p-2.5"><p class="text-[9.5px] font-black uppercase text-slate-400">Average Prep Time</p><p class="pos-num text-[16px] font-black text-slate-900" x-text="activeEmployee.performance.avgPrepTime + ' min'"></p></div>
                    </div>
                </template>
                <x-admin.empty-state icon="chart" title="No performance data for this role" x-show="!activeEmployee.performance" />
            </div>
        </div>
    </template>
    <x-slot:footer>
        <button type="button" @click="openEdit(activeEmployee)" class="h-9 w-full rounded-md border border-slate-300 bg-white text-[11.5px] font-bold text-slate-700 hover:border-slate-900">Edit Employee</button>
    </x-slot:footer>
</x-pos.dialog>


{{-- Add / Edit Employee --}}
<x-pos.dialog name="form" width="max-w-lg" title="Employee Details">
    <div class="grid grid-cols-2 gap-3 p-4">
        <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Name</label><input x-model="draft.name" data-autofocus class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" /></div>
        <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Phone</label><input x-model="draft.phone" inputmode="tel" maxlength="10" class="pos-num h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" /></div>
        <div class="col-span-2"><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Email</label><input x-model="draft.email" class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" /></div>
        <div class="col-span-2"><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Address</label><textarea x-model="draft.address" rows="2" class="w-full resize-none rounded-md border border-slate-300 bg-white px-2.5 py-2 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none"></textarea></div>
        <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Role</label><select x-model="draft.role" class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none"><template x-for="r in roles" :key="r"><option x-text="r"></option></template></select></div>
        <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Shift</label><select x-model="draft.shift" class="h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none"><template x-for="[k,s] in Object.entries(shiftTypes)" :key="k"><option :value="k" x-text="s.label"></option></template></select></div>
        <div><label class="mb-1 block text-[10.5px] font-black uppercase tracking-wide text-slate-600">Joining Date</label><input x-model="draft.joiningDate" type="date" class="pos-num h-10 w-full rounded-md border border-slate-300 bg-white px-2.5 text-[12.5px] font-medium focus:border-slate-900 focus:outline-none" /></div>
    </div>
    <x-slot:footer>
        <div class="flex gap-2">
            <button type="button" @click="back()" class="h-10 flex-1 rounded-md border border-slate-300 bg-white text-[12px] font-bold uppercase tracking-wide text-slate-700 hover:border-slate-900">Cancel</button>
            <button type="button" @click="saveEmployee()" :disabled="!draft.name?.trim() || (draft.phone || '').trim().length < 10" class="h-10 flex-1 rounded-md bg-slate-900 text-[12px] font-black uppercase tracking-wide text-white hover:bg-slate-800 disabled:cursor-not-allowed disabled:bg-slate-300">
                <span x-text="draft.id ? 'Save Changes' : 'Add Employee'"></span>
            </button>
        </div>
    </x-slot:footer>
</x-pos.dialog>
