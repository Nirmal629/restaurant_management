import { overlayMixin, paginationMixin, money, formatDate } from '../shared/kit.js';
import { CANCEL_REASONS, FLOORS, OCCASIONS, OPERATOR, RESERVATIONS, SOURCES, TABLES, VENUE, WAITERS } from './demo-data.js';

export default function reservationsApp() {
    return {
        ...overlayMixin(),
        ...paginationMixin(8),

        venue: VENUE,
        operator: OPERATOR,
        floors: FLOORS,
        tables: TABLES,
        sources: SOURCES,
        occasions: OCCASIONS,
        waiters: WAITERS,
        cancelReasons: CANCEL_REASONS,
        money,
        formatDate,

        reservations: RESERVATIONS.map((r) => ({ ...r, history: [...r.history] })),
        todayIso: new Date().toISOString().slice(0, 10),

        view: 'today', // today | list | calendar
        calMode: 'month', // day | week | month
        calSelected: new Date().toISOString().slice(0, 10),
        calCursor: new Date(),

        query: '',
        statusFilter: 'all',
        sourceFilter: 'all',
        dateFrom: '',
        dateTo: '',
        loading: false,

        openRowMenu: null,
        activeId: null,
        createDraft: {},
        cancelDraft: { id: null, reason: '' },
        seatDraft: { id: null, table: null },
        findDraft: { guests: 2, date: '', time: '', floor: 'all' },

        init() {
            this.simulateLoad();
        },
        simulateLoad() {
            this.loading = true;
            setTimeout(() => (this.loading = false), 500);
        },

        reservation(id) {
            return this.reservations.find((r) => r.id === id);
        },
        get activeReservation() {
            return this.reservation(this.activeId);
        },
        floorLabel(key) {
            return this.floors.find((f) => f.key === key)?.label || key;
        },
        prettyDate(iso) {
            if (iso === this.todayIso) return 'Today';
            return this.formatDate(iso);
        },
        timeLabel(t) {
            const [h, m] = t.split(':').map(Number);
            const period = h >= 12 ? 'PM' : 'AM';
            const h12 = h % 12 === 0 ? 12 : h % 12;
            return `${h12}:${String(m).padStart(2, '0')} ${period}`;
        },

        /* ---------------------------------------------------------------
           Status vocabulary
           --------------------------------------------------------------- */
        statusLabel(s) {
            return { pending: 'Pending', confirmed: 'Confirmed', arrived: 'Arrived', seated: 'Seated', completed: 'Completed', cancelled: 'Cancelled', no_show: 'No Show' }[s] || s;
        },
        statusClass(s) {
            return {
                pending: 'border-slate-300 bg-slate-100 text-slate-600',
                confirmed: 'border-sky-300 bg-sky-50 text-sky-800',
                arrived: 'border-amber-400 bg-amber-50 text-amber-800',
                seated: 'border-emerald-400 bg-emerald-50 text-emerald-800',
                completed: 'border-slate-300 bg-slate-100 text-slate-500',
                cancelled: 'border-rose-300 bg-rose-50 text-rose-700',
                no_show: 'border-rose-400 bg-rose-100 text-rose-800',
            }[s] || 'border-slate-300 bg-slate-100 text-slate-600';
        },

        /* ---------------------------------------------------------------
           Today + summary metrics
           --------------------------------------------------------------- */
        get todaysReservations() {
            return this.reservations.filter((r) => r.date === this.todayIso).sort((a, b) => a.time.localeCompare(b.time));
        },
        get summary() {
            const t = this.todaysReservations;
            return {
                today: t.length,
                confirmed: t.filter((r) => r.status === 'confirmed').length,
                arrived: t.filter((r) => r.status === 'arrived').length,
                seated: t.filter((r) => r.status === 'seated').length,
                noShow: t.filter((r) => r.status === 'no_show').length,
            };
        },

        /* ---------------------------------------------------------------
           List view filtering + pagination
           --------------------------------------------------------------- */
        get filteredList() {
            let list = [...this.reservations];
            if (this.statusFilter !== 'all') list = list.filter((r) => r.status === this.statusFilter);
            if (this.sourceFilter !== 'all') list = list.filter((r) => r.source === this.sourceFilter);
            if (this.dateFrom) list = list.filter((r) => r.date >= this.dateFrom);
            if (this.dateTo) list = list.filter((r) => r.date <= this.dateTo);
            if (this.query.trim()) {
                const q = this.query.trim().toLowerCase();
                list = list.filter((r) => [r.id, r.customer, r.phone, r.table].filter(Boolean).join(' ').toLowerCase().includes(q));
            }
            return list.sort((a, b) => (a.date + a.time).localeCompare(b.date + b.time));
        },
        get pagedList() {
            return this.pageSlice(this.filteredList);
        },
        clearFilters() {
            this.query = '';
            this.statusFilter = 'all';
            this.sourceFilter = 'all';
            this.dateFrom = '';
            this.dateTo = '';
            this.page = 1;
        },

        /* ---------------------------------------------------------------
           Calendar
           --------------------------------------------------------------- */
        countFor(iso) {
            return this.reservations.filter((r) => r.date === iso && r.status !== 'cancelled').length;
        },
        get monthCells() {
            const cursor = new Date(this.calCursor);
            const first = new Date(cursor.getFullYear(), cursor.getMonth(), 1);
            const startOffset = first.getDay();
            const daysInMonth = new Date(cursor.getFullYear(), cursor.getMonth() + 1, 0).getDate();
            const cells = [];
            for (let i = 0; i < startOffset; i++) cells.push(null);
            for (let d = 1; d <= daysInMonth; d++) {
                const iso = new Date(cursor.getFullYear(), cursor.getMonth(), d).toISOString().slice(0, 10);
                cells.push({ day: d, iso, count: this.countFor(iso) });
            }
            return cells;
        },
        get monthLabel() {
            return this.calCursor.toLocaleDateString('en-IN', { month: 'long', year: 'numeric' });
        },
        shiftMonth(delta) {
            const d = new Date(this.calCursor);
            d.setMonth(d.getMonth() + delta);
            this.calCursor = d;
        },
        get weekDays() {
            const base = new Date(this.calSelected);
            const start = new Date(base);
            start.setDate(base.getDate() - base.getDay());
            return Array.from({ length: 7 }, (_, i) => {
                const d = new Date(start);
                d.setDate(start.getDate() + i);
                const iso = d.toISOString().slice(0, 10);
                return { iso, label: d.toLocaleDateString('en-IN', { weekday: 'short', day: 'numeric' }), items: this.reservations.filter((r) => r.date === iso && r.status !== 'cancelled').sort((a, b) => a.time.localeCompare(b.time)) };
            });
        },
        selectDay(iso) {
            this.calSelected = iso;
            this.calMode = 'day';
        },
        get dayReservations() {
            return this.reservations.filter((r) => r.date === this.calSelected && r.status !== 'cancelled').sort((a, b) => a.time.localeCompare(b.time));
        },

        /* ---------------------------------------------------------------
           Detail drawer
           --------------------------------------------------------------- */
        openDetail(r) {
            this.openRowMenu = null;
            this.activeId = r.id;
            this.open('detail');
        },
        log(r, text) {
            r.history.unshift({ at: 'Just now', text });
        },

        /* ---------------------------------------------------------------
           Lifecycle actions
           --------------------------------------------------------------- */
        confirmReservation(r) {
            r.status = 'confirmed';
            this.log(r, 'Marked Confirmed');
            this.notify(`${r.id} confirmed`, 'success');
        },
        markArrived(r) {
            r.status = 'arrived';
            this.log(r, 'Marked Arrived');
            this.notify(`${r.customer} marked arrived`);
        },
        openSeat(r) {
            this.openRowMenu = null;
            this.seatDraft = { id: r.id, table: r.table };
            this.open('seat');
        },
        confirmSeat() {
            const r = this.reservation(this.seatDraft.id);
            if (!r || !this.seatDraft.table) return;
            r.table = this.seatDraft.table;
            r.status = 'seated';
            this.log(r, `Seated at ${this.seatDraft.table}`);
            this.closeAll();
            this.notify(`${r.customer} seated at ${r.table} — open POS to start the order`, 'success');
        },
        openChangeTable(r) {
            this.openRowMenu = null;
            this.seatDraft = { id: r.id, table: r.table };
            this.swap('seat');
        },
        markNoShow(r) {
            r.status = 'no_show';
            this.log(r, 'Marked No Show');
            this.notify(`${r.customer} marked no-show`, 'warn');
        },
        openCancel(r) {
            this.openRowMenu = null;
            this.cancelDraft = { id: r.id, reason: '' };
            this.open('cancel');
        },
        confirmCancel() {
            const r = this.reservation(this.cancelDraft.id);
            if (!r || !this.cancelDraft.reason) return;
            r.status = 'cancelled';
            this.log(r, `Cancelled — ${this.cancelDraft.reason}`);
            this.closeAll();
            this.notify(`${r.id} cancelled`, 'warn');
        },

        /* ---------------------------------------------------------------
           Create / edit
           --------------------------------------------------------------- */
        openCreate() {
            this.openRowMenu = null;
            this.createDraft = { id: null, customer: '', phone: '', email: '', date: this.todayIso, time: '19:00', guests: 2, floor: 'ground', table: null, occasion: 'None', request: '', source: 'Phone', notes: '' };
            this.open('create');
        },
        openEdit(r) {
            this.openRowMenu = null;
            this.createDraft = { id: r.id, customer: r.customer, phone: r.phone, email: r.email, date: r.date, time: r.time, guests: r.guests, floor: r.floor, table: r.table, occasion: r.occasion, request: r.request, source: r.source, notes: '' };
            this.open('create');
        },
        saveReservation() {
            const d = this.createDraft;
            if (!d.customer.trim() || d.phone.trim().length < 10) return;
            if (d.id) {
                const r = this.reservation(d.id);
                Object.assign(r, d);
                this.log(r, 'Reservation details updated');
                this.notify(`${r.id} updated`, 'success');
            } else {
                const r = { id: 'RES-' + (300 + this.reservations.length), status: 'pending', deposit: 0, createdBy: this.operator.name, history: [{ at: 'Just now', text: `Reservation created via ${d.source}` }], ...d };
                this.reservations.unshift(r);
                this.notify(`${r.id} created`, 'success');
            }
            this.closeAll();
        },

        /* ---------------------------------------------------------------
           Find available table
           --------------------------------------------------------------- */
        openFindTable() {
            this.findDraft = { guests: 2, date: this.todayIso, time: '19:00', floor: 'all' };
            this.open('find');
        },
        get findResults() {
            const busy = new Set(
                this.reservations
                    .filter((r) => r.date === this.findDraft.date && r.table && ['confirmed', 'arrived', 'seated'].includes(r.status))
                    .map((r) => r.table)
            );
            return this.tables.filter((t) => !busy.has(t.id) && t.seats >= this.findDraft.guests && (this.findDraft.floor === 'all' || t.floor === this.findDraft.floor));
        },
        pickFoundTable(t) {
            this.createDraft.table = t.id;
            this.createDraft.floor = t.floor;
            this.swap('create');
        },
    };
}
