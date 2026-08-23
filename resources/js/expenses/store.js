import { overlayMixin, paginationMixin, money } from '../shared/kit.js';

const boot = window.expenseModule || {};
const routeConfig = window.expenseRoutes || {};

export default function expensesApp() {
    return {
        ...overlayMixin(),
        ...paginationMixin(10),
        venue: boot.venue || { name: 'Restaurant', branch: 'Main Branch' },
        operator: boot.operator || { name: 'System' },
        categories: boot.categories || [],
        methods: boot.methods || [],
        threshold: boot.threshold || 10000,
        routes: routeConfig,
        money,

        expenses: (boot.expenses || []).map((e) => ({ ...e })),
        timeline: boot.timeline || {},

        query: '',
        categoryFilter: 'all',
        statusFilter: 'all',
        openRowMenu: null,
        activeId: null,
        expenseModal: null,
        draft: {},
        rejectDraft: { id: null, reason: '' },
        saving: false,

        statusLabel(s) {
            return { draft: 'Draft', approved: 'Approved', rejected: 'Rejected', paid: 'Paid' }[s] || s;
        },
        statusClass(s) {
            return {
                draft: 'border-slate-300 bg-slate-100 text-slate-600',
                approved: 'border-sky-300 bg-sky-50 text-sky-800',
                rejected: 'border-rose-300 bg-rose-50 text-rose-700',
                paid: 'border-emerald-400 bg-emerald-50 text-emerald-800',
            }[s] || 'border-slate-300 bg-slate-100 text-slate-600';
        },
        openExpenseModal(name) {
            this.expenseModal = name;
            this.stack = [name];
            this.$nextTick(() => this.focusFirst());
        },
        closeAll() {
            this.expenseModal = null;
            this.stack = [];
            this.openRowMenu = null;
        },

        expense(id) {
            return this.expenses.find((e) => e.id === id);
        },
        get activeExpense() {
            return this.expense(this.activeId);
        },

        get filtered() {
            let list = [...this.expenses];
            if (this.categoryFilter !== 'all') list = list.filter((e) => e.category === this.categoryFilter);
            if (this.statusFilter !== 'all') list = list.filter((e) => e.status === this.statusFilter);
            if (this.query.trim()) {
                const q = this.query.trim().toLowerCase();
                list = list.filter((e) => [e.id, e.description, e.vendor, e.employee].join(' ').toLowerCase().includes(q));
            }
            return list;
        },
        get paged() {
            return this.pageSlice(this.filtered);
        },
        clearFilters() {
            this.query = '';
            this.categoryFilter = 'all';
            this.statusFilter = 'all';
            this.page = 1;
        },

        get summary() {
            const today = new Date().toLocaleDateString('en-GB');
            const todayRows = this.expenses.filter((e) => e.date === today);
            const byCat = {};
            this.expenses.forEach((e) => (byCat[e.category] = (byCat[e.category] || 0) + Number(e.amount || 0)));
            const top = Object.entries(byCat).sort((a, b) => b[1] - a[1])[0];
            return {
                today: todayRows.reduce((s, e) => s + Number(e.amount || 0), 0),
                month: this.expenses.reduce((s, e) => s + Number(e.amount || 0), 0),
                pending: this.expenses.filter((e) => e.status === 'draft').length,
                topCategory: top ? top[0] : '-',
            };
        },

        openDetail(e) {
            this.openRowMenu = null;
            this.activeId = e.id;
            this.openExpenseModal('detail');
        },
        openCreate() {
            this.openRowMenu = null;
            this.activeId = null;
            this.draft = { id: null, dbId: null, date: new Date().toLocaleDateString('en-GB'), category: this.categories[0] || '', amount: '', method: this.methods[0] || 'Cash', vendor: '', description: '', reference: '', notes: '', receipt: false };
            this.openExpenseModal('form');
        },
        openEdit(e) {
            this.openRowMenu = null;
            this.activeId = e.id;
            this.draft = { ...e };
            this.openExpenseModal('form');
        },
        get needsApproval() {
            return Number(this.draft.amount) > this.threshold;
        },
        async saveExpense() {
            const d = this.draft;
            if (!d.category || !Number(d.amount) || !d.description?.trim()) return;
            this.saving = true;
            try {
                const url = d.dbId ? `${this.routes.base}/${d.dbId}` : this.routes.base;
                const response = await this.request(url, d.dbId ? 'PUT' : 'POST', d);
                this.upsertExpense(response.expense);
                this.timeline = response.timeline || this.timeline;
                this.closeAll();
                this.notify(response.message || 'Expense saved', 'success');
            } catch (error) {
                this.notify(error.message || 'Could not save expense', 'error');
            } finally {
                this.saving = false;
            }
        },
        async approve(e) {
            await this.changeStatus(e, 'approved');
        },
        async markPaid(e) {
            await this.changeStatus(e, 'paid');
        },
        openReject(e) {
            this.openRowMenu = null;
            this.activeId = e.id;
            this.rejectDraft = { id: e.id, dbId: e.dbId, reason: '' };
            this.openExpenseModal('reject');
        },
        async confirmReject() {
            if (!this.rejectDraft.reason?.trim()) return;
            await this.changeStatus(this.rejectDraft, 'rejected', { reason: this.rejectDraft.reason });
        },
        async deleteExpense(e) {
            this.openRowMenu = null;
            if (!window.confirm(`Delete ${e.id}?`)) return;
            try {
                const response = await this.request(`${this.routes.base}/${e.dbId}`, 'DELETE');
                this.expenses = response.expenses || this.expenses.filter((x) => x.id !== e.id);
                this.timeline = response.timeline || this.timeline;
                this.closeAll();
                this.notify(response.message || 'Expense deleted', 'success');
            } catch (error) {
                this.notify(error.message || 'Could not delete expense', 'error');
            }
        },
        async changeStatus(e, status, extra = {}) {
            this.openRowMenu = null;
            this.saving = true;
            try {
                const response = await this.request(`${this.routes.base}/${e.dbId}/status`, 'PATCH', { status, ...extra });
                this.upsertExpense(response.expense);
                this.timeline = response.timeline || this.timeline;
                this.closeAll();
                this.notify(response.message || 'Status updated', status === 'rejected' ? 'warn' : 'success');
            } catch (error) {
                this.notify(error.message || 'Could not update status', 'error');
            } finally {
                this.saving = false;
            }
        },
        upsertExpense(expense) {
            const index = this.expenses.findIndex((e) => e.id === expense.id);
            if (index === -1) this.expenses.unshift(expense);
            else this.expenses.splice(index, 1, expense);
        },
        printExpenses() {
            window.print();
        },
        async request(url, method, body = null) {
            const response = await fetch(url, {
                method,
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: body ? JSON.stringify(body) : null,
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(data.message || Object.values(data.errors || {})[0]?.[0] || 'Request failed');
            return data;
        },
    };
}
