import { overlayMixin, paginationMixin, initials } from '../shared/kit.js';
import { ACTIONS, EMPLOYEES, MODULES, ROLES, ROLE_DEFAULTS, SHIFT_TYPES, VENUE } from './demo-data.js';

export default function employeesApp() {
    return {
        ...overlayMixin(),
        ...paginationMixin(10),
        venue: VENUE,
        roles: ROLES,
        modules: MODULES,
        actions: ACTIONS,
        roleDefaults: ROLE_DEFAULTS,
        shiftTypes: SHIFT_TYPES,
        initials,

        employees: EMPLOYEES.map((e) => ({ ...e, permissionOverrides: { ...e.permissionOverrides }, activity: [...(e.activity || [])] })),
        query: '',
        roleFilter: 'all',
        openRowMenu: null,
        activeId: null,
        activeTab: 'profile',
        draft: {},

        statusLabel(s) {
            return { active: 'Active', inactive: 'Inactive', suspended: 'Suspended' }[s] || s;
        },
        statusClass(s) {
            return { active: 'border-emerald-400 bg-emerald-50 text-emerald-800', inactive: 'border-slate-300 bg-slate-100 text-slate-500', suspended: 'border-rose-400 bg-rose-100 text-rose-800' }[s] || 'border-slate-300 bg-slate-100 text-slate-600';
        },

        employee(id) {
            return this.employees.find((e) => e.id === id);
        },
        get activeEmployee() {
            return this.employee(this.activeId);
        },

        get filtered() {
            let list = [...this.employees];
            if (this.roleFilter !== 'all') list = list.filter((e) => e.role === this.roleFilter);
            if (this.query.trim()) {
                const q = this.query.trim().toLowerCase();
                list = list.filter((e) => [e.name, e.employeeId, e.phone].join(' ').toLowerCase().includes(q));
            }
            return list;
        },
        get paged() {
            return this.pageSlice(this.filtered);
        },
        clearFilters() {
            this.query = '';
            this.roleFilter = 'all';
            this.page = 1;
        },

        get summary() {
            return {
                total: this.employees.length,
                active: this.employees.filter((e) => e.status === 'active').length,
                waiters: this.employees.filter((e) => e.role === 'Waiter').length,
                onShiftNow: this.employees.filter((e) => e.status === 'active' && (e.shift === 'evening' || e.shift === 'fullday')).length,
            };
        },

        openProfile(e) {
            this.openRowMenu = null;
            this.activeId = e.id;
            this.activeTab = 'profile';
            this.open('profile');
        },
        openCreate() {
            this.openRowMenu = null;
            this.draft = { id: null, name: '', phone: '', email: '', address: '', role: this.roles[3], shift: 'fullday', joiningDate: '2026-08-23' };
            this.open('form');
        },
        openEdit(e) {
            this.openRowMenu = null;
            this.draft = { id: e.id, name: e.name, phone: e.phone, email: e.email, address: e.address, role: e.role, shift: e.shift, joiningDate: e.joiningDate };
            this.swap('form');
        },
        saveEmployee() {
            const d = this.draft;
            if (!d.name.trim() || d.phone.trim().length < 10) return;
            if (d.id) {
                Object.assign(this.employee(d.id), d);
                this.notify(`${d.name} updated`, 'success');
            } else {
                this.employees.unshift({ ...d, employeeId: 'EMP-' + String(100 + this.employees.length).padStart(3, '0'), status: 'active', pinSet: false, permissionOverrides: {}, activity: [{ at: 'Just now', text: 'Employee record created' }] });
                this.notify(`${d.name} added`, 'success');
            }
            this.closeAll();
        },
        setStatus(e, status) {
            this.openRowMenu = null;
            e.status = status;
            e.activity.unshift({ at: 'Just now', text: `Account marked ${this.statusLabel(status)}` });
            this.notify(`${e.name} marked ${this.statusLabel(status)}`, status === 'suspended' ? 'warn' : 'success');
        },

        /* Permissions */
        effectivePermissions(e) {
            const base = this.roleDefaults[e.role] || {};
            const merged = {};
            this.modules.forEach((m) => (merged[m] = new Set(base[m] || [])));
            Object.entries(e.permissionOverrides || {}).forEach(([m, acts]) => {
                merged[m] = new Set(acts);
            });
            return merged;
        },
        hasPermission(e, mod, action) {
            return this.effectivePermissions(e)[mod]?.has(action) || false;
        },
        togglePermission(e, mod, action) {
            const current = new Set(this.effectivePermissions(e)[mod] || []);
            current.has(action) ? current.delete(action) : current.add(action);
            e.permissionOverrides = { ...e.permissionOverrides, [mod]: [...current] };
        },
        isOverridden(e, mod) {
            return !!e.permissionOverrides?.[mod];
        },
        resetModuleToDefault(e, mod) {
            const o = { ...e.permissionOverrides };
            delete o[mod];
            e.permissionOverrides = o;
        },

        /* Shifts */
        setShift(e, key) {
            e.shift = key;
            this.notify(`${e.name}'s shift set to ${this.shiftTypes[key].label}`);
        },
    };
}
