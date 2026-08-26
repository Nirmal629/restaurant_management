import { CONFIG, FLOORS, RESERVATIONS, TABLES, VENUE, OPERATOR, WAITERS } from './demo-data.js';
import { subscribeRealtime } from '../shared/realtime.js';

const boot = window.tablesModule || {};
const routes = window.tablesRoutes || {};
const csrf = () => document.querySelector("meta[name='csrf-token']")?.content || "";

const inr = (n) => '₹' + Number(n || 0).toLocaleString('en-IN', { maximumFractionDigits: 0 });

/**
 * Alpine root for Floor & Table Management.
 *
 * Everything here is presentation state, mirroring the seam pattern used in
 * resources/js/pos/store.js — anywhere real persistence, seating logic, or
 * payment capture will eventually land is marked `// backend:`.
 */
export default function tablesApp(posUrl) {
    return {
        /* ---------------------------------------------------------------
           Reference data
           --------------------------------------------------------------- */
        venue: boot.venue || VENUE,
        operator: boot.operator || OPERATOR,
        floors: boot.floors || FLOORS,
        waiterNames: WAITERS,
        config: CONFIG,
        posUrl,

        /* ---------------------------------------------------------------
           Mutable dummy state
           --------------------------------------------------------------- */
        tables: (boot.tables || TABLES).map((t) => ({ ...t, items: t.items ? [...t.items] : [] })),
        reservations: (boot.reservations || RESERVATIONS).map((r) => ({ ...r })),
        sectionLabels: { ground: ['Window Side', 'Entrance'], first: [], outdoor: ['Garden'], vip: [] },
        nextTableSeq: 25,
        nextReservationSeq: 209,
        clock: '',

        /* ---------------------------------------------------------------
           UI state
           --------------------------------------------------------------- */
        activeFloor: 'all',
        statusFilter: 'all',
        waiterFilter: 'all',
        capacityFilter: 'all',
        kitchenReadyOnly: false,
        query: '',
        editLayout: false,
        dragId: null,
        stack: [],
        overlay: null,
        toast: null,
        saving: false,
        savingAction: '',

        // active record for whichever dialog is open
        activeTableId: null,
        activeGroupId: null,
        activeReservationId: null,

        // drafts
        startDraft: { tableId: null, guests: 2, waiter: '', customer: '', note: '' },
        reserveDraft: { tableId: null, customer: '', phone: '', time: '', guests: 2, notes: '' },
        transferDraft: { fromId: null, toId: null },
        mergeDraft: { primaryId: null, secondaryId: null },
        waiterDraft: { tableId: null, waiter: '' },
        addTableDraft: { number: '', name: '', floor: 'ground', capacity: 4, shape: 'square' },
        editTableDraft: { id: null, number: '', name: '', floor: 'ground', capacity: 4, shape: 'square', active: true },
        addFloorDraft: { name: '', description: '', order: '', active: true },
        findDraft: { guests: 2, floor: 'all' },
        newSectionLabel: '',

        /* ---------------------------------------------------------------
           Lifecycle
           --------------------------------------------------------------- */
        init() {
            this.tick();
            setInterval(() => this.tick(), 30000);
            this._unsubscribeRealtime = subscribeRealtime(['tables', 'reservations', 'orders', 'billing'], () => this.refreshTables());
        },
        async refreshTables() {
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
        tick() {
            this.clock = new Date().toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit', hour12: false });
        },

        /* ---------------------------------------------------------------
           Overlay stack (same shape as the POS terminal, so <x-pos.dialog>
           and other shared atoms work unmodified against this store too)
           --------------------------------------------------------------- */
        open(name) {
            if (this.overlay === name) return;
            this.stack.push(name);
            this.overlay = name;
            this.$nextTick(() => this.focusFirst());
        },
        swap(name) {
            if (this.stack.length) this.stack[this.stack.length - 1] = name;
            else this.stack.push(name);
            this.overlay = name;
            this.$nextTick(() => this.focusFirst());
        },
        back() {
            this.stack.pop();
            this.overlay = this.stack.length ? this.stack[this.stack.length - 1] : null;
        },
        closeAll() {
            this.stack = [];
            this.overlay = null;
        },
        focusFirst() {
            const root = this.$refs.overlayRoot;
            if (!root) return;
            const el = root.querySelector('[data-autofocus]');
            if (el) el.focus();
        },
        notify(message, tone = 'info') {
            this.toast = { message, tone };
            clearTimeout(this._toastTimer);
            this._toastTimer = setTimeout(() => (this.toast = null), 2600);
        },
        applyServerState(data) {
            if (!data) return;
            if (data.venue) this.venue = data.venue;
            if (data.operator) this.operator = data.operator;
            if (data.floors) this.floors = data.floors;
            if (data.tables) this.tables = data.tables.map((t) => ({ ...t, items: t.items ? [...t.items] : [] }));
            if (data.reservations) this.reservations = data.reservations.map((r) => ({ ...r }));
        },
        async api(url, options = {}) {
            if (!url || this.saving) return null;
            this.saving = true;
            this.savingAction = options.action || '';
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
                    this.notify(first || data.message || 'Table update failed', 'warn');
                    return null;
                }
                this.applyServerState(data);
                return data;
            } finally {
                this.saving = false;
                this.savingAction = '';
            }
        },

        /* ---------------------------------------------------------------
           Lookups
           --------------------------------------------------------------- */
        table(id) {
            return this.tables.find((t) => t.id === id);
        },
        tableUrl(t, suffix = '') {
            const id = typeof t === 'object' ? t.dbId : this.table(t)?.dbId;
            return id ? `${routes.base}/${id}${suffix}` : null;
        },
        reservation(id) {
            if (id == null) return null;
            return this.reservations.find((r) => String(r.id) === String(id) || String(r.dbId) === String(id));
        },
        reservationFor(tableId) {
            const t = this.table(tableId);
            if (t?.reservationId) return this.reservation(t.reservationId);
            return this.reservations.find((r) => String(r.tableId) === String(tableId) || String(r.table) === String(tableId)) || null;
        },
        activeReservation() {
            const reservation = this.reservation(this.activeReservationId) || this.reservationFor(this.activeTableId);
            if (reservation) return reservation;

            const card = this.activeCard;
            if (!card || card.status !== 'reserved') return null;

            return {
                id: card.reservationId || 'Reservation',
                tableId: card.id,
                customer: card.reservationCustomer || card.customer || 'Guest',
                phone: card.reservationPhone || '',
                date: card.reservationDate || '',
                time: card.reservationTime || 'Reserved',
                guests: card.reservationGuests || card.guests || card.seats || 0,
                notes: card.reservationNotes || '',
                status: 'RESERVED',
                synthetic: true,
            };
        },
        reservationTime(card) {
            return this.reservationFor(card.id)?.time || card.reservationTime || 'Reserved';
        },
        reservationGuestLine(card) {
            const reservation = this.reservationFor(card.id);
            const customer = reservation?.customer || card.reservationCustomer || 'Guest';
            const guests = reservation?.guests || card.reservationGuests || card.seats || 0;
            return `${customer} - ${guests} guest${Number(guests) === 1 ? '' : 's'}`;
        },
        bookingDates() {
            const today = new Date();
            const tomorrow = new Date(today);
            tomorrow.setDate(today.getDate() + 1);
            return {
                today: today.toISOString().slice(0, 10),
                tomorrow: tomorrow.toISOString().slice(0, 10),
            };
        },
        tableIdsForCard(card) {
            if (!card) return [];
            if (card.kind === 'group' && card.groupId) return this.groupMembers(card.groupId).map((t) => t.id);
            return [card.id];
        },
        upcomingBookings(card) {
            const dates = this.bookingDates();
            const ids = this.tableIdsForCard(card).map(String);
            return this.reservations
                .filter((r) => ids.includes(String(r.tableId || r.table)))
                .filter((r) => [dates.today, dates.tomorrow].includes(r.date))
                .filter((r) => !['CANCELLED', 'NO_SHOW', 'COMPLETED'].includes(String(r.status || '').toUpperCase()))
                .sort((a, b) => `${a.date} ${a.time}`.localeCompare(`${b.date} ${b.time}`));
        },
        bookingsForDay(card, day) {
            const dates = this.bookingDates();
            return this.upcomingBookings(card).filter((r) => r.date === dates[day]);
        },
        bookingDayLabel(day) {
            return day === 'today' ? 'Today' : 'Tomorrow';
        },
        bookingTimeLabel(time) {
            if (!time || !String(time).includes(':')) return '-';
            const [hours, minutes] = String(time).split(':').map(Number);
            if (!Number.isFinite(hours) || !Number.isFinite(minutes)) return '-';
            const period = hours >= 12 ? 'PM' : 'AM';
            const h12 = hours % 12 === 0 ? 12 : hours % 12;
            return `${h12}:${String(minutes).padStart(2, '0')} ${period}`;
        },
        bookingStatusLabel(status) {
            return String(status || 'PENDING').replace('_', ' ').toLowerCase();
        },
        cleaningLabel(card) {
            return typeof card.since === 'number' ? `${card.since} min` : 'Cleaning';
        },
        floorLabel(key) {
            return this.floors.find((f) => f.key === key)?.label || key;
        },
        groupMembers(groupId) {
            return this.tables.filter((t) => String(t.groupId || '') === String(groupId || ''));
        },
        primaryOfGroup(groupId) {
            return this.groupMembers(groupId).find((t) => t.groupPrimary) || this.groupMembers(groupId)[0];
        },
        orderSourceOfGroup(groupId) {
            const members = this.groupMembers(groupId);
            return members.find((t) => t.orderId) || members.find((t) => t.groupPrimary) || members[0];
        },
        orderIdFor(card) {
            if (!card) return null;
            if (card.orderId) return card.orderId;
            if (!card.groupId) return null;
            return this.orderSourceOfGroup(card.groupId)?.orderId || null;
        },
        isLong(t) {
            return typeof t.since === 'number' && t.status === 'occupied' && t.since >= this.config.longRunningMinutes;
        },

        /* ---------------------------------------------------------------
           Cards: grouped (merged) tables collapse into one visual unit
           --------------------------------------------------------------- */
        /** Unfiltered — the source of truth for both the visible grid and any open dialog. */
        get rawCards() {
            const seen = new Set();
            const cards = [];
            for (const t of this.tables) {
                if (seen.has(t.id)) continue;
                if (t.groupId) {
                    const members = this.groupMembers(t.groupId);
                    members.forEach((m) => seen.add(m.id));
                    const primary = this.primaryOfGroup(t.groupId);
                    const orderSource = this.orderSourceOfGroup(t.groupId) || primary;
                    cards.push({
                        ...primary,
                        orderId: orderSource?.orderId,
                        orderCode: orderSource?.orderCode,
                        guests: orderSource?.guests,
                        waiter: orderSource?.waiter,
                        customer: orderSource?.customer,
                        amount: orderSource?.amount,
                        since: orderSource?.since,
                        kitchen: orderSource?.kitchen,
                        items: orderSource?.items || [],
                        kind: 'group',
                        groupId: t.groupId,
                        members,
                        ids: members.map((m) => m.id),
                        label: members.map((m) => m.id).join(' + '),
                        seats: members.reduce((s, m) => s + m.seats, 0),
                    });
                } else {
                    seen.add(t.id);
                    cards.push({ kind: 'single', ids: [t.id], label: t.id, ...t });
                }
            }
            return cards;
        },
        /** What the floor map actually renders — rawCards narrowed by the active filters. */
        get cardGroups() {
            return this.rawCards.filter((c) => this.passesFilters(c));
        },
        passesFilters(c) {
            if (this.activeFloor !== 'all' && c.floor !== this.activeFloor) return false;
            if (this.statusFilter !== 'all' && c.status !== this.statusFilter) return false;
            if (this.waiterFilter !== 'all' && c.waiter !== this.waiterFilter) return false;
            if (this.capacityFilter !== 'all') {
                const [lo, hi] = this.capacityFilter.split('-').map(Number);
                if (hi ? (c.seats < lo || c.seats > hi) : c.seats < lo) return false;
            }
            if (this.kitchenReadyOnly && !(c.kitchen?.ready > 0)) return false;
            if (this.query.trim()) {
                const q = this.query.trim().toLowerCase();
                const hay = [c.label, c.orderCode, c.customer, c.waiter].filter(Boolean).join(' ').toLowerCase();
                if (!hay.includes(q)) return false;
            }
            return true;
        },
        clearFilters() {
            this.statusFilter = 'all';
            this.waiterFilter = 'all';
            this.capacityFilter = 'all';
            this.kitchenReadyOnly = false;
            this.query = '';
        },

        /* ---------------------------------------------------------------
           Floor sidebar counts
           --------------------------------------------------------------- */
        floorTableCount(key) {
            return this.tables.filter((t) => t.floor === key).length;
        },
        floorOccupiedCount(key) {
            return this.tables.filter((t) => t.floor === key && t.status === 'occupied').length;
        },

        /* ---------------------------------------------------------------
           Status summary — always computed, never hardcoded
           --------------------------------------------------------------- */
        get summary() {
            const singles = this.tables; // physical tables, for seat/guest/status math
            const by = (s) => singles.filter((t) => t.status === s).length;
            return {
                total: singles.length,
                available: by('available'),
                occupied: by('occupied'),
                reserved: by('reserved'),
                billing: by('billing'),
                cleaning: by('cleaning'),
                disabled: by('disabled'),
                guestsSeated: singles.reduce((s, t) => s + (t.status === 'occupied' || t.status === 'billing' ? t.guests || 0 : 0), 0),
                revenue: singles.reduce((s, t) => s + (t.amount || 0), 0),
                totalCapacity: singles.reduce((s, t) => s + t.seats, 0),
            };
        },
        toggleStatusFilter(s) {
            this.statusFilter = this.statusFilter === s ? 'all' : s;
        },

        /* ---------------------------------------------------------------
           Waiter load — used by the filter bar and the change-waiter modal
           --------------------------------------------------------------- */
        get waiterStats() {
            return this.waiterNames.map((name) => ({
                name,
                tables: this.tables.filter((t) => t.waiter === name && (t.status === 'occupied' || t.status === 'billing')).length,
                guests: this.tables.filter((t) => t.waiter === name).reduce((s, t) => s + (t.guests || 0), 0),
            }));
        },

        /* ---------------------------------------------------------------
           Table statuses — glyph + label, never colour-only
           --------------------------------------------------------------- */
        statusLabel(s) {
            return { available: 'Available', occupied: 'Occupied', reserved: 'Reserved', billing: 'Bill Ready', cleaning: 'Cleaning', disabled: 'Disabled' }[s] || s;
        },
        statusGlyph(s) {
            return { available: '●', occupied: '◑', reserved: '◷', billing: '¤', cleaning: '✦', disabled: '✕' }[s] || '•';
        },
        statusClass(s) {
            return {
                available: 'border-emerald-400 bg-emerald-50 text-emerald-800',
                occupied: 'border-sky-400 bg-sky-50 text-sky-800',
                reserved: 'border-violet-400 bg-violet-50 text-violet-800',
                billing: 'border-amber-400 bg-amber-50 text-amber-800',
                cleaning: 'border-slate-400 bg-slate-100 text-slate-600',
                disabled: 'border-rose-300 bg-rose-50 text-rose-600',
            }[s] || 'border-slate-300 bg-slate-100 text-slate-600';
        },
        cardShapeClass(shape) {
            return { round: 'rounded-2xl', rect: 'rounded-lg', square: 'rounded-md' }[shape] || 'rounded-md';
        },
        money: inr,

        /* ---------------------------------------------------------------
           Primary tap flow — routes by status
           --------------------------------------------------------------- */
        openCard(card) {
            if (this.editLayout) return; // edit mode taps are for dragging, not opening
            this.activeTableId = card.kind === 'group' ? null : card.id;
            this.activeGroupId = card.kind === 'group' ? card.groupId : null;
            switch (card.status) {
                case 'available': return this.open('quick');
                case 'occupied': return this.open('details');
                case 'reserved': this.activeReservationId = card.reservationId || this.reservationFor(card.id)?.id || null; return this.open('reservation');
                case 'billing': return this.open('billing');
                case 'cleaning': return this.open('cleaning');
                case 'disabled': return this.open('disabled');
            }
        },
        /** Right-click / long-press — status-aware quick action list, any status. */
        openContextMenu(card, event) {
            event?.preventDefault();
            this.activeTableId = card.kind === 'group' ? null : card.id;
            this.activeGroupId = card.kind === 'group' ? card.groupId : null;
            this.open('context');
        },
        get activeCard() {
            if (this.activeGroupId) return this.rawCards.find((c) => c.groupId === this.activeGroupId);
            return this.rawCards.find((c) => c.kind === 'single' && c.id === this.activeTableId);
        },
        contextActions(card) {
            if (!card) return [];
            const c = [];
            if (card.status === 'occupied' || card.status === 'billing') {
                c.push({ key: 'pos', label: 'Open POS' }, { key: 'order', label: 'View Order' }, { key: 'waiter', label: 'Change Waiter' }, { key: 'transfer', label: 'Transfer Table' }, { key: 'merge', label: 'Merge Table' });
            }
            if (card.status === 'available') {
                c.push({ key: 'start', label: 'Start Dine-In Order' }, { key: 'reserve', label: 'Create Reservation' }, { key: 'move', label: 'Move Table' });
            }
            if (card.status === 'reserved') {
                c.push({ key: 'arrived', label: 'Mark Arrived' }, { key: 'seat', label: 'Seat Customer' });
            }
            if (card.status === 'billing') {
                c.push({ key: 'payment', label: 'Take Payment' });
            }
            if (card.status === 'occupied' || card.status === 'billing') {
                c.push({ key: 'clean', label: 'Mark Cleaning' });
            }
            if (card.status === 'cleaning') {
                c.push({ key: 'available', label: 'Mark Available' });
            }
            if (card.status !== 'disabled') {
                c.push({ key: 'disable', label: 'Disable Table', danger: true });
            } else {
                c.push({ key: 'enable', label: 'Mark Available' });
            }
            c.push({ key: 'edit', label: 'Edit Table' });
            return c;
        },
        runContextAction(key) {
            const card = this.activeCard;
            this.back();
            if (!card) return;
            switch (key) {
                case 'pos': return this.goToPos(card);
                case 'order': return this.open('details');
                case 'waiter': return this.openChangeWaiter(card);
                case 'transfer': return this.openTransfer(card);
                case 'merge': return this.openMerge(card);
                case 'start': return this.openStart(card);
                case 'reserve': return this.openReserve(card);
                case 'move': return this.notify('Drag the table in Edit Layout mode to move it');
                case 'arrived': return this.markArrived(card.reservationId);
                case 'seat': return this.seatFromReservation(card.reservationId);
                case 'payment': return this.goToPos(card);
                case 'clean': return this.markCleaningStart(card);
                case 'available': case 'enable': return this.markAvailable(card);
                case 'disable': return this.markDisabled(card);
                case 'edit': return this.openEditTable(card);
            }
        },

        /* ---------------------------------------------------------------
           Cross-module navigation (real link — POS route already exists)
           --------------------------------------------------------------- */
        goToPos(card) {
            const orderId = this.orderIdFor(card);
            window.location.href = orderId ? `${this.posUrl}?order=${orderId}` : this.posUrl;
        },
        addItems(card) {
            const orderId = this.orderIdFor(card);
            if (!orderId) {
                this.notify('No active order found for this table.', 'warn');
                return;
            }
            window.location.href = `${this.posUrl}?order=${orderId}&mode=items`;
        },
        viewOrder(card) {
            const orderId = this.orderIdFor(card);
            window.location.href = orderId && routes.orders ? `${routes.orders}?order=${orderId}` : (routes.orders || '#');
        },

        /* ---------------------------------------------------------------
           Start dine-in order
           --------------------------------------------------------------- */
        openStart(card) {
            this.startDraft = { tableId: card.id, guests: Math.min(2, card.seats), waiter: this.operator.name, customer: '', note: '' };
            this.swap('start');
        },
        async confirmStart() {
            const t = this.table(this.startDraft.tableId);
            if (!t) return;
            const data = await this.api(this.tableUrl(t, '/start'), {
                method: 'POST',
                body: JSON.stringify({
                    guests: this.startDraft.guests,
                    customer: this.startDraft.customer,
                    note: this.startDraft.note,
                }),
            });
            if (data?.redirect) window.location.href = data.redirect;
        },

        /* ---------------------------------------------------------------
           Create reservation (from an available table)
           --------------------------------------------------------------- */
        openReserve(card) {
            this.reserveDraft = { tableId: card.id, customer: '', phone: '', time: '', guests: Math.min(2, card.seats), notes: '' };
            this.swap('reserve');
        },
        async confirmReserve() {
            const d = this.reserveDraft;
            if (!d.customer.trim() || !d.time.trim()) return;
            const t = this.table(d.tableId);
            if (!t) return;
            const data = await this.api(this.tableUrl(t, '/reserve'), {
                method: 'POST',
                body: JSON.stringify({ ...d, time: this.normalizeTime(d.time), date: new Date().toISOString().slice(0, 10) }),
            });
            if (!data) return;
            this.closeAll();
            this.notify(data.message || `Reservation created for ${t.id}`, 'success');
        },
        normalizeTime(value) {
            const text = String(value || '').trim();
            const ampm = text.match(/^(\d{1,2})(?::(\d{2}))?\s*(AM|PM)$/i);
            if (ampm) {
                let hour = Number(ampm[1]);
                const minute = ampm[2] || '00';
                const suffix = ampm[3].toUpperCase();
                if (suffix === 'PM' && hour < 12) hour += 12;
                if (suffix === 'AM' && hour === 12) hour = 0;
                return `${String(hour).padStart(2, '0')}:${minute}`;
            }
            const clock = text.match(/^(\d{1,2}):(\d{2})$/);
            if (clock) return `${String(Number(clock[1])).padStart(2, '0')}:${clock[2]}`;
            return text;
        },

        /* ---------------------------------------------------------------
           Reservation lifecycle
           --------------------------------------------------------------- */
        markArrived(resId) {
            const r = this.reservation(resId);
            if (r) r.status = 'ARRIVED';
            this.notify(`${r?.customer} marked arrived`);
        },
        seatFromReservation(resId) {
            const r = this.reservation(resId);
            if (!r) return;
            const t = this.table(r.tableId);
            if (!t) return;
            this.startDraft = { tableId: t.id, guests: r.guests, waiter: this.operator.name, customer: r.customer, note: r.notes };
            r.status = 'SEATED';
            this.swap('start');
        },
        openEditReservation(resId) {
            this.activeReservationId = resId;
            this.swap('reservation');
        },
        cancelReservation(resId) {
            const r = this.reservation(resId);
            if (!r) return;
            const t = this.table(r.tableId);
            if (t) { t.status = 'available'; t.reservationId = null; }
            r.status = 'CANCELLED';
            this.closeAll();
            this.notify(`Reservation ${resId} cancelled`, 'warn');
        },
        openChangeReservationTable(resId) {
            const r = this.reservation(resId);
            if (!r) return;
            this.transferDraft = { fromId: r.tableId, toId: null };
            this.swap('transfer');
        },

        /* ---------------------------------------------------------------
           Disable / enable / cleaning
           --------------------------------------------------------------- */
        async markDisabled(card) {
            const t = this.table(card.id);
            if (!t) return;
            const data = await this.api(this.tableUrl(t, '/status'), { method: 'PATCH', action: 'disable', body: JSON.stringify({ status: 'disabled' }) });
            if (!data) return;
            this.closeAll();
            this.notify(`${t.id} marked disabled`, 'warn');
        },
        async markAvailable(card) {
            const t = this.table(card.id);
            if (!t) return;
            const data = await this.api(this.tableUrl(t, '/status'), { method: 'PATCH', action: 'available', body: JSON.stringify({ status: 'available' }) });
            if (!data) return;
            this.closeAll();
            this.notify(`${card.label} is now available`, 'success');
        },
        async markCleaningStart(card) {
            const t = this.table(card.id);
            if (!t) return;
            const data = await this.api(this.tableUrl(t, '/status'), { method: 'PATCH', action: 'cleaning', body: JSON.stringify({ status: 'cleaning' }) });
            if (!data) return;
            this.closeAll();
            this.notify(`${card.label} sent for cleaning`);
        },

        /* ---------------------------------------------------------------
           Billing
           --------------------------------------------------------------- */
        printBill(card) {
            const lines = (card.items || []).map((item) => `
                <tr><td>${item.name}</td><td style="text-align:right">${item.qty}</td><td>${item.state || ''}</td></tr>
            `).join('');
            const popup = window.open('', '_blank', 'width=420,height=640');
            if (!popup) {
                this.notify('Allow popups to print the running bill.', 'warn');
                return;
            }
            popup.document.write(`
                <html><head><title>Running Bill ${card.orderCode || card.label}</title>
                <style>body{font-family:Arial,sans-serif;padding:18px}h1{font-size:18px}table{width:100%;border-collapse:collapse}td,th{border-bottom:1px solid #ddd;padding:8px;text-align:left}.total{font-size:18px;font-weight:700;text-align:right;margin-top:16px}</style>
                </head><body>
                <h1>Running Bill</h1>
                <p><strong>Table:</strong> ${card.label}<br><strong>Order:</strong> ${card.orderCode || '-'}<br><strong>Waiter:</strong> ${card.waiter || '-'}</p>
                <table><thead><tr><th>Item</th><th style="text-align:right">Qty</th><th>Status</th></tr></thead><tbody>${lines || '<tr><td colspan="3">No items punched yet.</td></tr>'}</tbody></table>
                <p class="total">Total: ${this.money(card.amount)}</p>
                </body></html>
            `);
            popup.document.close();
            popup.focus();
            popup.print();
        },
        cancelBill(card) {
            const t = this.table(card.id);
            if (t) t.status = 'occupied';
            this.closeAll();
            this.notify(`Bill for ${card.label} reopened`, 'warn');
        },
        markSettled(card) {
            // backend: this happens automatically once PaymentDrawer settles in POS
            this.markAvailable(card);
        },

        /* ---------------------------------------------------------------
           Change waiter
           --------------------------------------------------------------- */
        openChangeWaiter(card) {
            this.waiterDraft = { tableId: card.id, waiter: card.waiter };
            this.swap('waiter');
        },
        async confirmWaiterChange() {
            const t = this.table(this.waiterDraft.tableId);
            if (!t || !this.waiterDraft.waiter) return;
            const data = await this.api(this.tableUrl(t, '/waiter'), {
                method: 'PATCH',
                action: 'waiter',
                body: JSON.stringify({ waiter: this.waiterDraft.waiter }),
            });
            if (!data) return;
            this.closeAll();
            this.notify(data.message || `${this.waiterDraft.waiter} is now serving ${t.id}`, 'success');
        },

        /* ---------------------------------------------------------------
           Transfer
           --------------------------------------------------------------- */
        openTransfer(card) {
            this.transferDraft = { fromId: card.id, toId: null };
            this.swap('transfer');
        },
        get transferTargets() {
            const from = this.table(this.transferDraft.fromId);
            if (!from) return [];
            const needed = from.guests || this.reservationFor(from.id)?.guests || 1;
            return this.tables.filter((t) => t.status === 'available' && t.id !== from.id && t.seats >= needed);
        },
        /** Reserved tables move only the reservation link; occupied tables move the whole running order. */
        async confirmTransfer() {
            const from = this.table(this.transferDraft.fromId);
            const to = this.table(this.transferDraft.toId);
            if (!from || !to) return;

            const data = await this.api(this.tableUrl(from, '/transfer'), {
                method: 'POST',
                action: 'transfer',
                body: JSON.stringify({ to: to.id }),
            });
            if (!data) return;

            this.closeAll();
            this.notify(data.message || `${from.id} transferred to ${to.id}`, 'success');
        },

        /* ---------------------------------------------------------------
           Merge / unmerge
           --------------------------------------------------------------- */
        openMerge(card) {
            this.mergeDraft = { primaryId: card.id, secondaryId: null };
            this.swap('merge');
        },
        get mergeTargets() {
            const primary = this.table(this.mergeDraft.primaryId);
            if (!primary) return [];
            return this.tables.filter((t) => t.status === 'available' && t.floor === primary.floor && t.id !== primary.id);
        },
        async confirmMerge() {
            const primary = this.table(this.mergeDraft.primaryId);
            const secondary = this.table(this.mergeDraft.secondaryId);
            if (!primary || !secondary) return;
            const data = await this.api(this.tableUrl(primary, '/merge'), {
                method: 'POST',
                action: 'merge',
                body: JSON.stringify({ with: secondary.id }),
            });
            if (!data) return;
            await this.refreshTables();
            this.closeAll();
            this.notify(data.message || `${primary.id} + ${secondary.id} merged`, 'success');
        },
        async generateBill(card) {
            const orderId = this.orderIdFor(card);
            if (!orderId) {
                this.notify('No active order found for this table.', 'warn');
                return;
            }
            const data = await this.api(`${routes.posOrders}/${orderId}/billing`, {
                method: 'POST',
                action: 'billing',
                body: JSON.stringify({}),
            });
            if (!data) return;
            window.location.href = data.redirect || '/billing';
        },
        openUnmerge(groupId) {
            this.activeGroupId = groupId;
            this.swap('unmerge');
        },
        confirmUnmerge(mode) {
            const members = this.groupMembers(this.activeGroupId);
            const primary = this.primaryOfGroup(this.activeGroupId);
            const others = members.filter((m) => m.id !== primary.id);
            others.forEach((t) => {
                t.groupId = undefined;
                t.groupPrimary = undefined;
                if (mode === 'move') {
                    t.status = primary.status;
                    t.waiter = primary.waiter;
                    t.customer = primary.customer;
                    t.orderCode = primary.orderCode;
                    t.since = primary.since;
                    t.guests = Math.floor((primary.guests || 2) / members.length);
                } else {
                    t.status = 'available';
                }
            });
            primary.groupId = undefined;
            primary.groupPrimary = undefined;
            this.closeAll();
            this.notify('Tables unmerged');
        },

        /* ---------------------------------------------------------------
           Find table
           --------------------------------------------------------------- */
        openFindTable() {
            this.findDraft = { guests: 2, floor: 'all' };
            this.open('find');
        },
        get recommendedTables() {
            return this.tables
                .filter((t) => t.status === 'available' && t.seats >= this.findDraft.guests)
                .filter((t) => this.findDraft.floor === 'all' || t.floor === this.findDraft.floor)
                .sort((a, b) => a.seats - b.seats)
                .slice(0, 6);
        },
        pickRecommended(t) {
            this.closeAll();
            this.openStart({ id: t.id, seats: t.seats });
        },

        /* ---------------------------------------------------------------
           Add / edit table & floor (frontend-only mutation)
           --------------------------------------------------------------- */
        openAddTable() {
            this.addTableDraft = { number: 'T' + this.nextTableSeq, name: '', floor: this.activeFloor !== 'all' ? this.activeFloor : 'ground', capacity: 4, shape: 'square' };
            this.open('addTable');
        },
        async confirmAddTable() {
            const d = this.addTableDraft;
            if (!d.number.trim()) return;
            const data = await this.api(routes.store, {
                method: 'POST',
                body: JSON.stringify({ code: d.number.trim(), name: d.name, floor: d.floor, seats: Number(d.capacity) || 2, shape: d.shape }),
            });
            if (!data) return;
            this.closeAll();
            this.notify(`${d.number} added to ${this.floorLabel(d.floor)}`, 'success');
        },
        openEditTable(card) {
            const t = this.table(card.id) || card;
            this.editTableDraft = { id: t.id, number: t.id, name: t.name || '', floor: t.floor, capacity: t.seats, shape: t.shape, active: t.status !== 'disabled' };
            this.swap('editTable');
        },
        async confirmEditTable() {
            const d = this.editTableDraft;
            const t = this.table(d.id);
            if (!t) return;
            const data = await this.api(this.tableUrl(t), {
                method: 'PUT',
                body: JSON.stringify({ name: d.name, floor: d.floor, seats: Number(d.capacity) || t.seats, shape: d.shape, active: d.active }),
            });
            if (!data) return;
            this.closeAll();
            this.notify(`${t.id} updated`, 'success');
        },
        openAddFloor() {
            this.addFloorDraft = { name: '', description: '', order: this.floors.length + 1, active: true };
            this.open('addFloor');
        },
        async confirmAddFloor() {
            const d = this.addFloorDraft;
            if (!d.name.trim()) return;
            const data = await this.api(routes.floors, {
                method: 'POST',
                body: JSON.stringify({ name: d.name, description: d.description, order: d.order, active: d.active }),
            });
            if (!data) return;
            this.closeAll();
            this.notify(`Floor "${d.name}" added`, 'success');
        },

        /* ---------------------------------------------------------------
           Floor layout editor — client-side drag reorder, nothing persisted
           --------------------------------------------------------------- */
        toggleEditLayout() {
            this.editLayout = !this.editLayout;
            if (!this.editLayout) this.notify('Layout changes are visual only in this preview');
        },
        addSectionLabel() {
            const label = this.newSectionLabel.trim();
            if (!label) return;
            const floor = this.activeFloor !== 'all' ? this.activeFloor : this.floors[0].key;
            (this.sectionLabels[floor] ||= []).push(label);
            this.newSectionLabel = '';
        },
        removeSectionLabel(floor, label) {
            this.sectionLabels[floor] = (this.sectionLabels[floor] || []).filter((l) => l !== label);
        },
        dragStart(id) {
            if (!this.editLayout) return;
            this.dragId = id;
        },
        dragOverTarget(targetId) {
            if (!this.editLayout || !this.dragId || this.dragId === targetId) return;
            const a = this.tables.findIndex((t) => t.id === this.dragId);
            const b = this.tables.findIndex((t) => t.id === targetId);
            if (a === -1 || b === -1) return;
            const [moved] = this.tables.splice(a, 1);
            this.tables.splice(b, 0, moved);
        },
        dragEnd() {
            this.dragId = null;
        },

        /* ---------------------------------------------------------------
           QR
           --------------------------------------------------------------- */
        openQr(card) {
            this.activeTableId = card.id;
            this.open('qr');
        },
        /** Deterministic pseudo-QR speckle, keyed by table id — placeholder only. */
        qrCells(id) {
            let seed = 0;
            for (const ch of id) seed = (seed * 31 + ch.charCodeAt(0)) >>> 0;
            const cells = [];
            for (let i = 0; i < 64; i++) {
                seed = (seed * 1103515245 + 12345) >>> 0;
                cells.push((seed >>> 16) % 5 === 0);
            }
            return cells;
        },
    };
}

