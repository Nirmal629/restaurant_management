import { overlayMixin, paginationMixin, money } from '../shared/kit.js';
import { APPROVAL_THRESHOLD, CATEGORIES, EXPENSES, OPERATOR, PAYMENT_METHODS, STATUS_TIMELINE, VENUE } from './demo-data.js';

export default function expensesApp() {
    return {
        ...overlayMixin(),
        ...paginationMixin(10),
        venue: VENUE,
        operator: OPERATOR,
        categories: CATEGORIES,
        methods: PAYMENT_METHODS,
        threshold: APPROVAL_THRESHOLD,
        money,

        expenses: EXPENSES.map((e) => ({ ...e })),
        timeline: STATUS_TIMELINE,

        query: '',
        categoryFilter: 'all',
        statusFilter: 'all',
        openRowMenu: null,
        activeId: null,
        draft: {},
        rejectDraft: { id: null, reason: '' },

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
            const today = this.expenses.filter((e) => e.date === '23/08/2026');
            const byCat = {};
            this.expenses.forEach((e) => (byCat[e.category] = (byCat[e.category] || 0) + e.amount));
            const top = Object.entries(byCat).sort((a, b) => b[1] - a[1])[0];
            return {
                today: today.reduce((s, e) => s + e.amount, 0),
                month: this.expenses.reduce((s, e) => s + e.amount, 0),
                pending: this.expenses.filter((e) => e.status === 'draft').length,
                topCategory: top ? top[0] : '—',
            };
        },

        openDetail(e) {
            this.openRowMenu = null;
            this.activeId = e.id;
            this.open('detail');
        },
        openCreate() {
            this.openRowMenu = null;
            this.draft = { id: null, date: '23/08/2026', category: this.categories[0], amount: '', method: 'Cash', vendor: '', description: '', reference: '', notes: '', receipt: false };
            this.open('form');
        },
        openEdit(e) {
            this.openRowMenu = null;
            this.draft = { ...e };
            this.swap('form');
        },
        get needsApproval() {
            return Number(this.draft.amount) > this.threshold;
        },
        saveExpense() {
            const d = this.draft;
            if (!d.category || !Number(d.amount) || !d.description.trim()) return;
            if (d.id) {
                Object.assign(this.expense(d.id), d);
                this.notify(`${d.id} updated`, 'success');
            } else {
                const status = Number(d.amount) > this.threshold ? 'draft' : 'paid';
                const e = { ...d, id: 'EXP-2026-' + (39 + this.expenses.length), employee: this.operator.name, branch: this.venue.branch, status };
                this.expenses.unshift(e);
                this.timeline[e.id] = [{ at: 'Just now', text: `Expense created by ${this.operator.name}` }];
                if (status === 'draft') this.timeline[e.id].push({ at: 'Just now', text: `Submitted for approval — exceeds ${this.money(this.threshold)} threshold` });
                this.notify(`${e.id} recorded${status === 'draft' ? ' — pending approval' : ''}`, 'success');
            }
            this.closeAll();
        },
        approve(e) {
            this.openRowMenu = null;
            e.status = 'approved';
            (this.timeline[e.id] ||= []).push({ at: 'Just now', text: 'Approved by Rakesh Singh' });
            this.notify(`${e.id} approved`, 'success');
        },
        markPaid(e) {
            this.openRowMenu = null;
            e.status = 'paid';
            (this.timeline[e.id] ||= []).push({ at: 'Just now', text: 'Marked as paid' });
            this.notify(`${e.id} marked paid`, 'success');
        },
        openReject(e) {
            this.openRowMenu = null;
            this.rejectDraft = { id: e.id, reason: '' };
            this.open('reject');
        },
        confirmReject() {
            const e = this.expense(this.rejectDraft.id);
            if (!e || !this.rejectDraft.reason.trim()) return;
            e.status = 'rejected';
            e.rejectReason = this.rejectDraft.reason;
            (this.timeline[e.id] ||= []).push({ at: 'Just now', text: `Rejected by ${this.operator.name} — ${this.rejectDraft.reason}` });
            this.closeAll();
            this.notify(`${e.id} rejected`, 'warn');
        },
    };
}
