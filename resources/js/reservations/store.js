import { overlayMixin, paginationMixin, money, formatDate } from '../shared/kit.js';
import { CANCEL_REASONS, FLOORS, OCCASIONS, OPERATOR, RESERVATIONS, SOURCES, TABLES, VENUE, WAITERS } from './demo-data.js';
import { subscribeRealtime } from '../shared/realtime.js';

const boot = window.reservationsModule || {};
const routes = window.reservationsRoutes || {};
const csrf = () => document.querySelector("meta[name='csrf-token']")?.content || "";

export default function reservationsApp() {
    return {
        ...overlayMixin(),
        ...paginationMixin(8),

        venue: boot.venue || VENUE,
        operator: boot.operator || OPERATOR,
        floors: boot.floors || FLOORS,
        tables: boot.tables || TABLES,
        sources: SOURCES,
        occasions: OCCASIONS,
        waiters: WAITERS,
        cancelReasons: CANCEL_REASONS,
        money,
        formatDate,

        reservations: (boot.reservations || RESERVATIONS).map((r) => normalizeReservation(r)),
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
        saving: false,

        openRowMenu: null,
        activeId: null,
        createDraft: {
            id: null,
            customer: '',
            phone: '',
            email: '',
            date: new Date().toISOString().slice(0, 10),
            time: '19:00',
            guests: 2,
            floor: 'ground',
            table: null,
            occasion: 'None',
            request: '',
            source: 'Phone',
            notes: '',
        },
        cancelDraft: { id: null, reason: '' },
        seatDraft: { id: null, table: null },
        findDraft: { guests: 2, date: '', time: '', floor: 'all' },

        init() {
            this.simulateLoad();
            this._unsubscribeRealtime = subscribeRealtime(['reservations', 'tables'], () => this.refreshReservations());
        },
        simulateLoad() {
            this.loading = true;
            setTimeout(() => (this.loading = false), 500);
        },
        async refreshReservations() {
            if (!routes.data || this.saving) return null;
            try {
                const response = await fetch(routes.data, { headers: { Accept: 'application/json' } });
                const data = await response.json().catch(() => ({}));
                if (!response.ok) return null;
                this.applyServerState(data);
                return data;
            } catch (error) {
                return null;
            }
        },
        applyServerState(data) {
            if (!data) return;
            if (data.venue) this.venue = data.venue;
            if (data.operator) this.operator = data.operator;
            if (data.floors) this.floors = data.floors;
            if (data.tables) this.tables = data.tables;
            if (data.reservations) this.reservations = data.reservations.map((r) => normalizeReservation(r));
        },
        async api(url, options = {}) {
            if (!url || this.saving) return null;
            this.saving = true;
            try {
                const response = await fetch(url, {
                    ...options,
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf(),
                        ...(options.headers || {}),
                    },
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    const first = data.errors ? Object.values(data.errors).flat()[0] : null;
                    this.notify(first || data.message || 'Reservation update failed', 'warn');
                    return null;
                }
                this.applyServerState(data);
                return data;
            } finally {
                this.saving = false;
            }
        },
        reservationUrl(r, suffix = '') {
            const id = typeof r === 'object' ? r.dbId : this.reservation(r)?.dbId;
            return id ? `${routes.base}/${id}${suffix}` : null;
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
            if (!t || typeof t !== 'string' || !t.includes(':')) return '-';
            const [h, m] = t.split(':').map(Number);
            if (!Number.isFinite(h) || !Number.isFinite(m)) return '-';
            const period = h >= 12 ? 'PM' : 'AM';
            const h12 = h % 12 === 0 ? 12 : h % 12;
            return `${h12}:${String(m).padStart(2, '0')} ${period}`;
        },
        cellDay(cell) {
            return cell ? cell.day : '';
        },
        cellCount(cell) {
            return cell ? cell.count : '';
        },
        reservationCustomer(id) {
            return this.reservation(id)?.customer || '';
        },
        canSaveDraft() {
            return !!String(this.createDraft.customer || '').trim()
                && String(this.createDraft.phone || '').trim().length >= 10
                && (!this.createDraft.table || this.tableAvailableForDraft(this.table(this.createDraft.table), this.createDraft));
        },
        table(id) {
            return this.reservableTables.find((t) => t.id === id || t.reserveCode === id) || this.tables.find((t) => t.id === id);
        },
        get reservableTables() {
            const seen = new Set();
            const cards = [];
            const rank = { disabled: 6, occupied: 5, billing: 4, cleaning: 3, reserved: 2, available: 1 };
            for (const table of this.tables) {
                if (seen.has(table.id)) continue;
                if (!table.groupId) {
                    cards.push({ ...table, reserveCode: table.id, members: [table.id] });
                    seen.add(table.id);
                    continue;
                }

                const members = this.tables.filter((t) => String(t.groupId || '') === String(table.groupId));
                members.forEach((member) => seen.add(member.id));
                const primary = members.find((t) => t.groupPrimary) || members[0];
                const status = members.reduce((carry, member) => (rank[member.status] || 0) > (rank[carry] || 0) ? member.status : carry, 'available');
                cards.push({
                    ...primary,
                    id: members.map((member) => member.id).join(' + '),
                    reserveCode: primary.id,
                    seats: members.reduce((sum, member) => sum + Number(member.seats || 0), 0),
                    status,
                    members: members.map((member) => member.id),
                    merged: true,
                });
            }

            return cards;
        },

        /* ---------------------------------------------------------------
           Status vocabulary
           --------------------------------------------------------------- */
        statusLabel(s) {
            return { pending: 'Pending', confirmed: 'Confirmed', arrived: 'Arrived', seated: 'Seated', completed: 'Completed', cancelled: 'Cancelled', no_show: 'No Show' }[s] || s;
        },
        tableStatusLabel(s) {
            return { available: 'Available', occupied: 'Occupied', reserved: 'Reserved', billing: 'Billing', cleaning: 'Cleaning', disabled: 'Disabled' }[s] || s;
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
        async confirmReservation(r) {
            const data = await this.api(this.reservationUrl(r, '/status'), { method: 'PATCH', body: JSON.stringify({ status: 'confirmed' }) });
            if (data) this.notify(`${r.id} confirmed`, 'success');
        },
        async markArrived(r) {
            const data = await this.api(this.reservationUrl(r, '/status'), { method: 'PATCH', body: JSON.stringify({ status: 'arrived' }) });
            if (data) this.notify(`${r.customer} marked arrived`);
        },
        openSeat(r) {
            this.openRowMenu = null;
            this.seatDraft = { id: r.id, table: r.table };
            this.open('seat');
        },
        async confirmSeat() {
            const r = this.reservation(this.seatDraft.id);
            if (!r || !this.seatDraft.table) return;
            const data = await this.api(this.reservationUrl(r, '/seat'), { method: 'POST', body: JSON.stringify({ table: this.seatDraft.table }) });
            if (!data) return;
            this.closeAll();
            this.notify(r.customer + ' seated at ' + this.seatDraft.table, 'success');
            if (data.redirect) window.location.href = data.redirect;
        },
        openChangeTable(r) {
            this.openRowMenu = null;
            this.seatDraft = { id: r.id, table: r.table };
            this.swap('seat');
        },
        async markNoShow(r) {
            const data = await this.api(this.reservationUrl(r, '/status'), { method: 'PATCH', body: JSON.stringify({ status: 'no_show' }) });
            if (data) this.notify(r.customer + ' marked no-show', 'warn');
        },
        openCancel(r) {
            this.openRowMenu = null;
            this.cancelDraft = { id: r.id, reason: '' };
            this.open('cancel');
        },
        async confirmCancel() {
            const r = this.reservation(this.cancelDraft.id);
            if (!r || !this.cancelDraft.reason) return;
            const data = await this.api(this.reservationUrl(r, '/status'), {
                method: 'PATCH',
                body: JSON.stringify({ status: 'cancelled', reason: this.cancelDraft.reason }),
            });
            if (!data) return;
            this.closeAll();
            this.notify(r.id + ' cancelled', 'warn');
        },

        /* ---------------------------------------------------------------
           Create / edit
           --------------------------------------------------------------- */
        openCreate() {
            this.openRowMenu = null;
            this.createDraft = {
                id: null,
                customer: '',
                phone: '',
                email: '',
                date: this.todayIso,
                time: '19:00',
                guests: 2,
                floor: this.floors[0]?.key || 'ground',
                table: null,
                occasion: this.occasions[0] || 'None',
                request: '',
                source: this.sources[0] || 'Phone',
                notes: '',
            };
            this.openOnly('create');
        },
        openEdit(r) {
            this.openRowMenu = null;
            this.createDraft = { id: r.id, customer: r.customer || '', phone: r.phone || '', email: r.email || '', date: r.date || this.todayIso, time: r.time || '19:00', guests: r.guests || 2, floor: r.floor || this.floors[0]?.key || 'ground', table: r.table || null, occasion: r.occasion || 'None', request: r.request || '', source: r.source || this.sources[0] || 'Phone', notes: '' };
            this.openOnly('create');
        },
        async saveReservation() {
            const d = this.createDraft;
            if (!d.customer.trim() || d.phone.trim().length < 10) return;
            if (d.table && !this.tableAvailableForDraft(this.table(d.table), d)) {
                this.notify(`${d.table} is not available for that date and time`, 'warn');
                return;
            }
            const body = JSON.stringify({ ...d, deposit: d.deposit || 0 });
            if (d.id) {
                const r = this.reservation(d.id);
                const data = await this.api(this.reservationUrl(r), { method: 'PUT', body });
                if (!data) return;
                this.notify(`${r.id} updated`, 'success');
            } else {
                const data = await this.api(routes.store, { method: 'POST', body });
                if (!data) return;
                this.notify(`${data.reservation?.id || 'Reservation'} created`, 'success');
            }
            this.closeAll();
        },

        /* ---------------------------------------------------------------
           Find available table
           --------------------------------------------------------------- */
        openFindTable() {
            this.findDraft = { guests: 2, date: this.todayIso, time: '19:00', floor: 'all' };
            this.openOnly('find');
        },
        openDraftFinder() {
            this.findDraft = {
                guests: this.createDraft.guests || 2,
                date: this.createDraft.date || this.todayIso,
                time: this.createDraft.time || '19:00',
                floor: this.createDraft.floor || 'all',
            };
            this.swap('find');
        },
        slotMinutes() {
            return 120;
        },
        slotDateTime(date, time) {
            const value = `${date || this.todayIso}T${time || '19:00'}`;
            const parsed = new Date(value);
            return Number.isNaN(parsed.getTime()) ? new Date(`${this.todayIso}T19:00`) : parsed;
        },
        slotConflict(tableId, draft) {
            const target = this.slotDateTime(draft.date, draft.time).getTime();
            const windowMs = this.slotMinutes() * 60000;
            return this.reservations.find((r) => {
                if (r.id === draft.id || r.table !== tableId || r.date !== draft.date) return false;
                if (!['pending', 'confirmed', 'arrived', 'seated'].includes(r.status)) return false;
                return Math.abs(this.slotDateTime(r.date, r.time).getTime() - target) < windowMs;
            });
        },
        slotConflictForTable(t, draft) {
            return (t?.members || [t?.id]).map((id) => this.slotConflict(id, draft)).find(Boolean) || null;
        },
        tableAvailableForDraft(t, draft) {
            return this.tableAvailability(t, draft).available;
        },
        tableAvailability(t, draft) {
            if (!t) return { available: false, label: 'Unknown table', tone: 'blocked' };
            if (t.seats < Number(draft.guests || 1)) return { available: false, label: `${t.seats} seats only`, tone: 'blocked' };
            if (t.status === 'disabled') return { available: false, label: 'Disabled', tone: 'blocked' };

            const conflict = this.slotConflictForTable(t, draft);
            if (conflict) return { available: false, label: `Booked ${this.timeLabel(conflict.time)}`, tone: 'booked' };

            const slot = this.slotDateTime(draft.date, draft.time);
            const now = new Date();
            const nearNow = draft.date === this.todayIso && slot.getTime() <= now.getTime() + this.slotMinutes() * 60000;
            if (nearNow && ['occupied', 'billing', 'cleaning'].includes(t.status)) {
                return { available: false, label: this.tableStatusLabel(t.status), tone: 'busy' };
            }

            if (['occupied', 'billing', 'cleaning'].includes(t.status)) {
                return { available: true, label: `${this.tableStatusLabel(t.status)} now, free for slot`, tone: 'future' };
            }

            if (t.status === 'reserved') return { available: true, label: 'Reserved now, free for slot', tone: 'future' };
            return { available: true, label: 'Available for slot', tone: 'available' };
        },
        tableOptionLabel(t, draft = this.createDraft) {
            const availability = this.tableAvailability(t, draft);
            return `${t.id} - ${t.seats} seats - ${this.tableStatusLabel(t.status)} - ${availability.label}`;
        },
        availabilityClass(t, draft = this.findDraft) {
            const availability = this.tableAvailability(t, draft);
            return {
                available: 'border-emerald-400 bg-emerald-50 hover:border-emerald-600',
                future: 'border-sky-300 bg-sky-50 hover:border-sky-500',
                booked: 'border-amber-400 bg-amber-50 opacity-70',
                busy: 'border-slate-300 bg-slate-100 opacity-70',
                blocked: 'border-rose-300 bg-rose-50 opacity-70',
            }[availability.tone] || 'border-slate-300 bg-white';
        },
        get preferredTableOptions() {
            return this.reservableTables
                .filter((t) => t.floor === this.createDraft.floor)
                .sort((a, b) => Number(!this.tableAvailableForDraft(a, this.createDraft)) - Number(!this.tableAvailableForDraft(b, this.createDraft)) || a.seats - b.seats || a.id.localeCompare(b.id));
        },
        get findResults() {
            return this.reservableTables
                .filter((t) => t.seats >= this.findDraft.guests && (this.findDraft.floor === 'all' || t.floor === this.findDraft.floor))
                .sort((a, b) => Number(!this.tableAvailableForDraft(a, this.findDraft)) - Number(!this.tableAvailableForDraft(b, this.findDraft)) || a.seats - b.seats || a.id.localeCompare(b.id));
        },
        pickFoundTable(t) {
            if (!this.tableAvailableForDraft(t, this.findDraft)) {
                this.notify(`${t.id} is not available for that slot`, 'warn');
                return;
            }
            this.createDraft.table = t.reserveCode || t.id;
            this.createDraft.floor = t.floor;
            this.createDraft.date = this.findDraft.date;
            this.createDraft.time = this.findDraft.time;
            this.createDraft.guests = this.findDraft.guests;
            this.swap('create');
        },
    };
}

function normalizeReservation(r = {}) {
    return {
        ...r,
        id: r.id || r.code || r.dbId || 'RES',
        customer: r.customer || r.customer_name || 'Guest',
        phone: r.phone || '',
        email: r.email || '',
        date: r.date || new Date().toISOString().slice(0, 10),
        time: r.time || '19:00',
        guests: Number(r.guests || 1),
        table: r.table || null,
        floor: r.floor || 'ground',
        source: r.source || 'Phone',
        status: r.status || 'pending',
        occasion: r.occasion || 'None',
        request: r.request || '',
        history: [...(r.history || [])],
    };
}

