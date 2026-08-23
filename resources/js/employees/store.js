import { overlayMixin, paginationMixin, initials, formatDate } from '../shared/kit.js';
import { ACTIONS, EMPLOYEES, MODULES, ROLES, ROLE_DEFAULTS, SHIFT_TYPES, VENUE } from './demo-data.js';

export default function employeesApp() {
    const boot = window.employeeModule || {};
    const routes = window.employeeRoutes || {};

    return {
        ...overlayMixin(),
        ...paginationMixin(10),
        venue: boot.venue || VENUE,
        roles: boot.roles || ROLES,
        modules: boot.modules || MODULES,
        actions: boot.actions || ACTIONS,
        roleDefaults: boot.roleDefaults || ROLE_DEFAULTS,
        shiftTypes: boot.shiftTypes || SHIFT_TYPES,
        initials,
        formatDate,

        employees: (boot.employees || EMPLOYEES).map((e) => ({ ...e, permissionOverrides: { ...(e.permissionOverrides || {}) }, activity: [...(e.activity || [])] })),
        query: '',
        roleFilter: 'all',
        openRowMenu: null,
        activeId: null,
        activeTab: 'profile',
        showProfile: false,
        showForm: false,
        draft: {},
        saving: false,
        suppressProfileUntil: 0,
        profileOpenArmed: false,

        statusLabel(s) {
            return { active: 'Active', inactive: 'Inactive', suspended: 'Suspended' }[s] || s;
        },
        statusClass(s) {
            return { active: 'border-emerald-400 bg-emerald-50 text-emerald-800', inactive: 'border-slate-300 bg-slate-100 text-slate-500', suspended: 'border-rose-400 bg-rose-100 text-rose-800' }[s] || 'border-slate-300 bg-slate-100 text-slate-600';
        },
        init() {
            this.sortEmployees();
        },
        open(name) {
            if (name === 'form') this.stack = this.stack.filter((item) => item !== 'profile');
            if (name === 'profile') this.stack = this.stack.filter((item) => item !== 'form');
            this.stack = this.stack.filter((item) => item !== name);
            this.stack.push(name);
            this.$nextTick(() => this.focusFirst());
        },
        swap(name) {
            if (this.stack.length) this.stack.pop();
            this.open(name);
        },
        back() {
            const current = this.overlay;
            this.stack = current ? this.stack.filter((item) => item !== current) : [];
            this.cleanupOverlayState();
        },
        closeAll() {
            this.stack = [];
            this.cleanupOverlayState();
        },
        closeEmployeeModal() {
            this.closeAll();
        },
        cleanupOverlayState() {
            if (!this.stack.includes('profile')) {
                this.showProfile = false;
                this.activeId = null;
                this.activeTab = 'profile';
            }
            if (!this.stack.includes('form')) {
                this.showForm = false;
                this.draft = {};
            }
            this.openRowMenu = null;
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

        armProfileOpen() {
            if (!this.overlay && Date.now() >= this.suppressProfileUntil) this.profileOpenArmed = true;
        },
        openProfile(e) {
            if (!this.profileOpenArmed || this.overlay || Date.now() < this.suppressProfileUntil) {
                this.profileOpenArmed = false;
                return;
            }
            this.profileOpenArmed = false;
            this.openRowMenu = null;
            this.stack = [];
            this.showForm = false;
            this.activeId = e.id;
            this.activeTab = 'profile';
            this.showProfile = true;
            this.stack = ['profile'];
        },
        openCreate() {
            this.stack = ['form'];
            this.openRowMenu = null;
            this.activeId = null;
            this.activeTab = 'profile';
            this.showProfile = false;
            this.showForm = true;
            this.profileOpenArmed = false;
            this.suppressProfileUntil = Date.now() + 500;
            this.draft = { id: null, name: '', phone: '', email: '', password: '', address: '', role: this.roles[0] || 'Staff', shift: 'fullday', joiningDate: new Date().toISOString().slice(0, 10) };
            this.$nextTick(() => this.focusFirst());
        },
        openEdit(e) {
            this.openRowMenu = null;
            this.draft = { id: e.id, name: e.name, phone: e.phone, email: e.email, password: '', address: e.address, role: e.role, shift: e.shift, joiningDate: e.joiningDate };
            this.stack = ['form'];
            this.showProfile = false;
            this.showForm = true;
            this.profileOpenArmed = false;
            this.$nextTick(() => this.focusFirst());
        },
        async saveEmployee() {
            const d = this.draft;
            if (!d.name.trim() || d.phone.trim().length < 10 || !d.email?.trim()) return;

            const url = d.id ? `${routes.update}/${d.id}` : routes.store;
            const method = d.id ? 'PUT' : 'POST';
            const payload = {
                name: d.name,
                phone: d.phone,
                email: d.email || null,
                address: d.address || null,
                role: d.role,
                shift: d.shift,
                joiningDate: d.joiningDate || null,
            };
            if (d.password?.trim()) payload.password = d.password;

            const result = await this.request(url, { method, body: JSON.stringify(payload) });
            if (!result) return;

            if (d.id) this.replaceEmployee(result.employee);
            else this.employees.unshift(this.normalizeEmployee(result.employee));

            this.sortEmployees();
            this.clearFilters();

            this.notify(result.message || `${d.name} saved`, 'success');
            this.activeId = null;
            this.showProfile = false;
            this.showForm = false;
            this.profileOpenArmed = false;
            this.suppressProfileUntil = Date.now() + 800;
            this.stack = [];
            this.closeAll();
        },
        async setStatus(e, status) {
            this.openRowMenu = null;
            const result = await this.request(`${routes.update}/${e.id}/status`, { method: 'PATCH', body: JSON.stringify({ status }) });
            if (!result) return;

            this.replaceEmployee(result.employee);
            this.notify(result.message || `${e.name} marked ${this.statusLabel(status)}`, status === 'suspended' ? 'warn' : 'success');
        },

        normalizeEmployee(employee) {
            return { ...employee, permissionOverrides: { ...(employee.permissionOverrides || {}) }, activity: [...(employee.activity || [])] };
        },
        replaceEmployee(employee) {
            const next = this.normalizeEmployee(employee);
            const index = this.employees.findIndex((e) => e.id === next.id);
            if (index >= 0) this.employees.splice(index, 1, next);
            else this.employees.unshift(next);

            if (this.activeId === next.id) this.activeId = next.id;
        },
        sortEmployees() {
            const codeNumber = (employee) => Number(String(employee.employeeId || '').replace(/\D/g, '')) || 0;
            this.employees.sort((a, b) => codeNumber(b) - codeNumber(a) || String(b.employeeId || '').localeCompare(String(a.employeeId || '')));
        },
        async request(url, options = {}) {
            if (!url || this.saving) return null;
            this.saving = true;

            try {
                const response = await fetch(url, {
                    ...options,
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        ...(options.headers || {}),
                    },
                });
                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    const firstError = data.errors ? Object.values(data.errors).flat()[0] : null;
                    this.notify(firstError || data.message || 'Employee update failed', 'warn');
                    return null;
                }

                return data;
            } catch (error) {
                this.notify('Network error while saving employee', 'warn');
                return null;
            } finally {
                this.saving = false;
            }
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
        async togglePermission(e, mod, action) {
            const previous = { ...(e.permissionOverrides || {}) };
            const current = new Set(this.effectivePermissions(e)[mod] || []);
            current.has(action) ? current.delete(action) : current.add(action);
            e.permissionOverrides = { ...e.permissionOverrides, [mod]: [...current] };
            await this.savePermissions(e, previous);
        },
        isOverridden(e, mod) {
            return !!e.permissionOverrides?.[mod];
        },
        async resetModuleToDefault(e, mod) {
            const previous = { ...(e.permissionOverrides || {}) };
            const o = { ...e.permissionOverrides };
            delete o[mod];
            e.permissionOverrides = o;
            await this.savePermissions(e, previous);
        },
        async savePermissions(e, previous = null) {
            const result = await this.request(`${routes.update}/${e.id}/permissions`, { method: 'PATCH', body: JSON.stringify({ permissionOverrides: e.permissionOverrides || {} }) });
            if (!result) {
                if (previous) e.permissionOverrides = previous;
                return;
            }

            this.replaceEmployee(result.employee);
            this.notify(result.message || 'Permissions updated', 'success');
        },

        /* Shifts */
        async setShift(e, key) {
            const result = await this.request(`${routes.update}/${e.id}/shift`, { method: 'PATCH', body: JSON.stringify({ shift: key }) });
            if (!result) return;

            this.replaceEmployee(result.employee);
            this.notify(result.message || `${e.name}'s shift set to ${this.shiftTypes[key].label}`, 'success');
        },
    };
}
