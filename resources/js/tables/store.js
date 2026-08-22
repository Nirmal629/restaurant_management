import { CONFIG, FLOORS, RESERVATIONS, TABLES, VENUE, OPERATOR, WAITERS } from './demo-data.js';

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
        venue: VENUE,
        operator: OPERATOR,
        floors: FLOORS,
        waiterNames: WAITERS,
        config: CONFIG,
        posUrl,

        /* ---------------------------------------------------------------
           Mutable dummy state
           --------------------------------------------------------------- */
        tables: TABLES.map((t) => ({ ...t, items: t.items ? [...t.items] : [] })),
        reservations: RESERVATIONS.map((r) => ({ ...r })),
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
        toast: null,

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
        },
        tick() {
            this.clock = new Date().toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit', hour12: false });
        },

        /* ---------------------------------------------------------------
           Overlay stack (same shape as the POS terminal, so <x-pos.dialog>
           and other shared atoms work unmodified against this store too)
           --------------------------------------------------------------- */
        get overlay() {
            return this.stack.length ? this.stack[this.stack.length - 1] : null;
        },
        open(name) {
            if (this.overlay === name) return;
            this.stack.push(name);
            this.$nextTick(() => this.focusFirst());
        },
        swap(name) {
            if (this.stack.length) this.stack[this.stack.length - 1] = name;
            else this.stack.push(name);
            this.$nextTick(() => this.focusFirst());
        },
        back() {
            this.stack.pop();
        },
        closeAll() {
            this.stack = [];
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

        /* ---------------------------------------------------------------
           Lookups
           --------------------------------------------------------------- */
        table(id) {
            return this.tables.find((t) => t.id === id);
        },
        reservation(id) {
            return this.reservations.find((r) => r.id === id);
        },
        reservationFor(tableId) {
            const t = this.table(tableId);
            return t?.reservationId ? this.reservation(t.reservationId) : null;
        },
        floorLabel(key) {
            return this.floors.find((f) => f.key === key)?.label || key;
        },
        groupMembers(groupId) {
            return this.tables.filter((t) => t.groupId === groupId);
        },
        primaryOfGroup(groupId) {
            return this.groupMembers(groupId).find((t) => t.groupPrimary) || this.groupMembers(groupId)[0];
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
                    cards.push({
                        ...primary,
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
                case 'reserved': this.activeReservationId = card.reservationId; return this.open('reservation');
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
            // backend: carry the table/order context into the POS session
            window.location.href = this.posUrl;
        },

        /* ---------------------------------------------------------------
           Start dine-in order
           --------------------------------------------------------------- */
        openStart(card) {
            this.startDraft = { tableId: card.id, guests: Math.min(2, card.seats), waiter: this.operator.name, customer: '', note: '' };
            this.swap('start');
        },
        confirmStart() {
            const t = this.table(this.startDraft.tableId);
            if (!t) return;
            // backend: create the order, then hand off to POS with this context
            t.status = 'occupied';
            t.guests = this.startDraft.guests;
            t.waiter = this.startDraft.waiter;
            t.customer = this.startDraft.customer || 'Walk-in';
            t.orderCode = 'ORD-' + (1040 + Math.floor(Math.random() * 900));
            t.amount = 0;
            t.since = 0;
            t.kitchen = { new: 0, prep: 0, ready: 0 };
            t.items = [];
            this.closeAll();
            this.goToPos(t);
        },

        /* ---------------------------------------------------------------
           Create reservation (from an available table)
           --------------------------------------------------------------- */
        openReserve(card) {
            this.reserveDraft = { tableId: card.id, customer: '', phone: '', time: '', guests: Math.min(2, card.seats), notes: '' };
            this.swap('reserve');
        },
        confirmReserve() {
            const d = this.reserveDraft;
            if (!d.customer.trim() || !d.time.trim()) return;
            const t = this.table(d.tableId);
            if (!t) return;
            const id = 'RES-' + this.nextReservationSeq++;
            this.reservations.push({ id, tableId: t.id, customer: d.customer, phone: d.phone, date: 'Today', time: d.time, guests: d.guests, notes: d.notes, status: 'CONFIRMED' });
            t.status = 'reserved';
            t.reservationId = id;
            this.closeAll();
            this.notify(`Reservation ${id} created for ${t.id}`, 'success');
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
        markDisabled(card) {
            const t = this.table(card.id);
            if (!t) return;
            t.status = 'disabled';
            this.closeAll();
            this.notify(`${t.id} marked disabled`, 'warn');
        },
        markAvailable(card) {
            (card.ids || [card.id]).forEach((id) => {
                const t = this.table(id);
                if (!t) return;
                Object.assign(t, { status: 'available', guests: undefined, waiter: undefined, customer: undefined, orderCode: undefined, amount: undefined, since: undefined, kitchen: undefined, items: [], groupId: undefined, groupPrimary: undefined, reservationId: undefined });
            });
            this.closeAll();
            this.notify(`${card.label} is now available`, 'success');
        },
        markCleaningStart(card) {
            (card.ids || [card.id]).forEach((id) => {
                const t = this.table(id);
                if (!t) return;
                Object.assign(t, { status: 'cleaning', since: 0, guests: undefined, waiter: undefined, customer: undefined, orderCode: undefined, amount: undefined, kitchen: undefined, items: [], groupId: undefined, groupPrimary: undefined });
            });
            this.closeAll();
            this.notify(`${card.label} sent for cleaning`);
        },

        /* ---------------------------------------------------------------
           Billing
           --------------------------------------------------------------- */
        generateBill(card) {
            (card.ids || [card.id]).forEach((id) => {
                const t = this.table(id);
                if (t) t.status = 'billing';
            });
            this.swap('billing');
        },
        printBill(card) {
            this.notify(`Bill for ${card.label} sent to printer`);
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
        confirmWaiterChange() {
            const t = this.table(this.waiterDraft.tableId);
            if (!t || !this.waiterDraft.waiter) return;
            t.waiter = this.waiterDraft.waiter;
            this.closeAll();
            this.notify(`${t.waiter} is now serving ${t.id}`);
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
        confirmTransfer() {
            const from = this.table(this.transferDraft.fromId);
            const to = this.table(this.transferDraft.toId);
            if (!from || !to) return;

            if (from.status === 'reserved') {
                const r = this.reservationFor(from.id);
                to.status = 'reserved';
                to.reservationId = from.reservationId;
                if (r) r.tableId = to.id;
                from.status = 'available';
                from.reservationId = undefined;
                this.closeAll();
                this.notify(`Reservation moved from ${from.id} to ${to.id}`, 'success');
                return;
            }

            const { status, guests, waiter, customer, orderCode, amount, since, kitchen, items } = from;
            Object.assign(to, { status, guests, waiter, customer, orderCode, amount, since, kitchen, items });
            Object.assign(from, { status: 'cleaning', since: 0, guests: undefined, waiter: undefined, customer: undefined, orderCode: undefined, amount: undefined, kitchen: undefined, items: [] });
            this.closeAll();
            this.notify(`Order moved from ${from.id} to ${to.id}`, 'success');
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
        confirmMerge() {
            const primary = this.table(this.mergeDraft.primaryId);
            const secondary = this.table(this.mergeDraft.secondaryId);
            if (!primary || !secondary) return;
            const groupId = 'G' + Date.now().toString(36);
            primary.groupId = groupId;
            primary.groupPrimary = true;
            secondary.groupId = groupId;
            secondary.groupPrimary = false;
            secondary.status = primary.status;
            this.closeAll();
            this.notify(`${primary.id} + ${secondary.id} merged`, 'success');
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
        confirmAddTable() {
            const d = this.addTableDraft;
            if (!d.number.trim()) return;
            // backend: persist the new table
            this.tables.push({ id: d.number.trim(), floor: d.floor, seats: Number(d.capacity) || 2, shape: d.shape, status: 'available', name: d.name });
            this.nextTableSeq++;
            this.closeAll();
            this.notify(`${d.number} added to ${this.floorLabel(d.floor)}`, 'success');
        },
        openEditTable(card) {
            const t = this.table(card.id) || card;
            this.editTableDraft = { id: t.id, number: t.id, name: t.name || '', floor: t.floor, capacity: t.seats, shape: t.shape, active: t.status !== 'disabled' };
            this.swap('editTable');
        },
        confirmEditTable() {
            const d = this.editTableDraft;
            const t = this.table(d.id);
            if (!t) return;
            t.name = d.name;
            t.floor = d.floor;
            t.seats = Number(d.capacity) || t.seats;
            t.shape = d.shape;
            t.status = d.active ? (t.status === 'disabled' ? 'available' : t.status) : 'disabled';
            this.closeAll();
            this.notify(`${t.id} updated`, 'success');
        },
        openAddFloor() {
            this.addFloorDraft = { name: '', description: '', order: this.floors.length + 1, active: true };
            this.open('addFloor');
        },
        confirmAddFloor() {
            const d = this.addFloorDraft;
            if (!d.name.trim()) return;
            const key = d.name.trim().toLowerCase().replace(/\s+/g, '-');
            this.floors.push({ key, label: d.name.trim() });
            this.sectionLabels[key] = [];
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
