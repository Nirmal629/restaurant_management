import { CANCEL_REASONS, CONFIG, OPERATOR, STATIONS, TICKETS, UNAVAILABLE_REASONS, VENUE } from './demo-data.js';
import { subscribeRealtime } from '../shared/realtime.js';

const boot = window.kdsModule || {};
const routes = window.kdsRoutes || {};
const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

/**
 * Alpine root for the Kitchen Display System.
 *
 * Presentation state only, same seam pattern as the POS and Floor/Table
 * stores — anywhere real dispatch, printing, or a realtime channel will
 * eventually land is marked `// backend:`. NEW → ACCEPTED → PREPARING move
 * a whole ticket at once; only inside PREPARING do individual items resolve,
 * which is what produces the "partially ready" state.
 */
export default function kdsApp() {
    return {
        /* ---------------------------------------------------------------
           Reference data
           --------------------------------------------------------------- */
        venue: boot.venue || VENUE,
        operator: boot.operator || OPERATOR,
        stations: STATIONS,
        config: CONFIG,
        cancelReasons: CANCEL_REASONS,
        unavailableReasons: UNAVAILABLE_REASONS,
        columns: [
            { key: 'new', label: 'New' },
            { key: 'accepted', label: 'Accepted' },
            { key: 'preparing', label: 'Preparing' },
            { key: 'ready', label: 'Ready' },
        ],

        /* ---------------------------------------------------------------
           Mutable dummy state
           --------------------------------------------------------------- */
        tickets: (boot.tickets || TICKETS).map((t) => ({ ...t, items: t.items.map((i) => ({ ...i, modifiers: [...(i.modifiers || [])] })) })),
        history: [],
        now: 0,
        clock: '',

        /* ---------------------------------------------------------------
           UI state
           --------------------------------------------------------------- */
        viewMode: 'kitchen', // kitchen | expeditor
        tvMode: false,
        isFullscreen: false,
        activeStation: 'all',
        statusFilter: 'all', // all | new | accepted | preparing | ready | delayed
        typeFilter: 'all',
        waitFilter: 'all',
        priorityFilter: 'all',
        sortMode: 'oldest',
        query: '',
        historyQuery: '',
        filtersOpen: false,
        soundOpen: false,
        soundOn: true,
        soundModes: { newKot: true, delayed: true, ready: true },
        connected: true,
        printerReady: true,
        notifications: [],
        toast: null,
        saving: false,

        stack: [],
        overlay: null,
        activeKot: null,
        reprintDraft: { kot: null, reason: '' },
        unavailableDraft: { kot: null, uid: null, reason: '' },
        priorityDraft: { kot: null, value: 'normal' },

        /* ---------------------------------------------------------------
           Lifecycle
           --------------------------------------------------------------- */
        init() {
            const base = Date.now();
            this.tickets.forEach((t) => {
                t.placedAt = t.placedAt || base - (t.placedMinutesAgo || 0) * 60000;
                t.acceptedAt = t.acceptedMinutesAgo != null ? base - t.acceptedMinutesAgo * 60000 : null;
                t.startedAt = t.startedMinutesAgo != null ? base - t.startedMinutesAgo * 60000 : null;
                t.readyAt = t.readyMinutesAgo != null ? base - t.readyMinutesAgo * 60000 : null;
                t.pickedUpAt = null;
                t.waiterNotified = !!t.waiterNotified;
            });
            this.tick();
            setInterval(() => this.tick(), 15000);
            this._unsubscribeRealtime = subscribeRealtime(['kitchen', 'orders', 'pos'], () => this.refreshKds());
        },
        tick() {
            this.now = Date.now();
            this.clock = new Date().toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit', hour12: false });
        },

        /* ---------------------------------------------------------------
           Overlay stack (same shape as the POS + Floor/Table stores)
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
        applyPayload(data) {
            if (!data) return;
            if (data.venue) this.venue = data.venue;
            if (data.operator) this.operator = data.operator;
            if (data.tickets) {
                const existing = new Set(this.tickets.map((t) => `${t.orderId}-${t.kot}`));
                this.tickets = data.tickets.map((t) => ({ ...t, items: t.items.map((i) => ({ ...i, modifiers: [...(i.modifiers || [])] })) }));
                this.tickets
                    .filter((t) => !existing.has(`${t.orderId}-${t.kot}`))
                    .forEach((t) => this.pushAlert(t));
            }
        },
        async refreshKds() {
            if (!routes.data || this.saving) return null;
            try {
                const response = await fetch(routes.data, { headers: { Accept: 'application/json' } });
                const data = await response.json().catch(() => ({}));
                if (!response.ok) return null;
                this.applyPayload(data);
                return data;
            } catch (error) {
                return null;
            }
        },
        async request(url, payload) {
            if (!url || this.saving) return null;
            this.saving = true;
            try {
                const response = await fetch(url, {
                    method: 'PATCH',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf(),
                    },
                    body: JSON.stringify(payload),
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    const first = data.errors ? Object.values(data.errors).flat()[0] : null;
                    this.notify(first || data.message || 'Kitchen update failed', 'warn');
                    return null;
                }
                this.applyPayload(data);
                return data;
            } finally {
                this.saving = false;
            }
        },

        /* ---------------------------------------------------------------
           Lookups & labels
           --------------------------------------------------------------- */
        ticket(kot) {
            return this.tickets.find((t) => t.kot === kot);
        },
        get activeTicket() {
            return this.ticket(this.activeKot);
        },
        stationLabel(key) {
            return this.stations.find((s) => s.key === key)?.label || key;
        },
        orderLabel(t) {
            if (t.orderType === 'dinein') return t.table;
            if (t.orderType === 'takeaway') return 'Token #' + t.token;
            return 'Order #' + t.token;
        },
        priorityLabel(p) {
            return { normal: 'Normal', priority: 'High Priority', rush: 'Rush', vip: 'VIP', waiting: 'Customer Waiting' }[p] || p;
        },

        /* ---------------------------------------------------------------
           Wait time — the single source of truth for delay visuals
           --------------------------------------------------------------- */
        waitMinutes(t) {
            return Math.max(0, Math.round((this.now - t.placedAt) / 60000));
        },
        readyForMinutes(t) {
            return t.readyAt ? Math.max(0, Math.round((this.now - t.readyAt) / 60000)) : 0;
        },
        waitLevel(t) {
            if (t.status === 'ready' || t.status === 'picked_up') return 'normal';
            const m = this.waitMinutes(t);
            if (m >= this.config.criticalMinutes) return 'critical';
            if (m >= this.config.warnMinutes) return 'warning';
            return 'normal';
        },
        waitLabel(t) {
            return { normal: 'On Time', warning: 'Delayed', critical: 'Critical' }[this.waitLevel(t)];
        },
        isDelayed(t) {
            return this.waitLevel(t) !== 'normal';
        },

        /* ---------------------------------------------------------------
           Item-level helpers
           --------------------------------------------------------------- */
        itemsForStation(t, station) {
            return station === 'all' ? t.items : t.items.filter((i) => i.station === station);
        },
        countReady(t) {
            return t.items.filter((i) => i.status === 'ready').length;
        },
        countTotal(t) {
            return t.items.filter((i) => i.status !== 'cancelled').length;
        },
        isPartiallyReady(t) {
            return t.status === 'preparing' && this.countReady(t) > 0 && this.countReady(t) < this.countTotal(t);
        },
        itemStatusClass(s) {
            return {
                pending: 'border-slate-300 bg-slate-100 text-slate-600',
                ready: 'border-emerald-400 bg-emerald-50 text-emerald-800',
                unavailable: 'border-rose-300 bg-rose-50 text-rose-600 line-through',
                cancel_requested: 'border-amber-400 bg-amber-50 text-amber-800',
                cancelled: 'border-rose-300 bg-rose-50 text-rose-600 line-through',
            }[s] || 'border-slate-300 bg-slate-100 text-slate-600';
        },
        itemStatusLabel(s) {
            return { pending: 'Pending', ready: 'Ready', unavailable: 'Unavailable', cancel_requested: 'Cancel Requested', cancelled: 'Cancelled' }[s] || s;
        },

        /* ---------------------------------------------------------------
           Station tab load
           --------------------------------------------------------------- */
        stationCount(key) {
            return this.tickets.filter(
                (t) => t.status !== 'picked_up' && (key === 'all' || t.items.some((i) => i.station === key && i.status !== 'cancelled'))
            ).length;
        },

        /* ---------------------------------------------------------------
           Filter + sort pipeline
           --------------------------------------------------------------- */
        get filteredTickets() {
            let list = this.tickets.filter((t) => t.status !== 'picked_up');
            if (this.activeStation !== 'all') list = list.filter((t) => t.items.some((i) => i.station === this.activeStation && i.status !== 'cancelled'));
            if (this.typeFilter !== 'all') list = list.filter((t) => t.orderType === this.typeFilter);
            if (this.priorityFilter !== 'all') list = list.filter((t) => t.priority === this.priorityFilter);
            if (this.waitFilter !== 'all') {
                list = list.filter((t) => {
                    const m = this.waitMinutes(t);
                    if (this.waitFilter === 'lt15') return m < 15;
                    if (this.waitFilter === '15-25') return m >= 15 && m < 25;
                    return m >= 25;
                });
            }
            if (this.statusFilter === 'delayed') list = list.filter((t) => this.isDelayed(t) && t.status !== 'ready');
            else if (this.statusFilter !== 'all') list = list.filter((t) => t.status === this.statusFilter);
            if (this.query.trim()) {
                const q = this.query.trim().toLowerCase();
                list = list.filter((t) => {
                    const hay = [t.table, t.token, t.orderCode, 'kot' + t.kot, t.waiter, ...t.items.map((i) => i.name)].filter(Boolean).join(' ').toLowerCase();
                    return hay.includes(q);
                });
            }
            return list;
        },
        sortedFor(status) {
            const rank = { rush: 0, vip: 1, waiting: 1, priority: 2, normal: 3 };
            const arr = this.filteredTickets.filter((t) => t.status === status);
            switch (this.sortMode) {
                case 'newest': return arr.sort((a, b) => b.placedAt - a.placedAt);
                case 'priority': return arr.sort((a, b) => (rank[a.priority] ?? 3) - (rank[b.priority] ?? 3) || a.placedAt - b.placedAt);
                case 'table': return arr.sort((a, b) => (a.table || a.token || '').localeCompare(b.table || b.token || ''));
                default: return arr.sort((a, b) => a.placedAt - b.placedAt);
            }
        },
        clearFilters() {
            this.typeFilter = 'all';
            this.waitFilter = 'all';
            this.priorityFilter = 'all';
            this.query = '';
            this.statusFilter = 'all';
        },

        /* ---------------------------------------------------------------
           Summary bar
           --------------------------------------------------------------- */
        get summary() {
            const active = this.tickets.filter((t) => t.status !== 'picked_up');
            const delayed = active.filter((t) => this.isDelayed(t) && t.status !== 'ready');
            const startedActive = active.filter((t) => t.startedAt);
            const runningPrep = startedActive.length
                ? Math.round(startedActive.reduce((s, t) => s + (this.now - t.startedAt) / 60000, 0) / startedActive.length)
                : 0;
            return {
                new: active.filter((t) => t.status === 'new').length,
                accepted: active.filter((t) => t.status === 'accepted').length,
                preparing: active.filter((t) => t.status === 'preparing').length,
                ready: active.filter((t) => t.status === 'ready').length,
                delayed: delayed.length,
                avgPrep: runningPrep,
                oldest: active.length ? Math.max(...active.map((t) => this.waitMinutes(t))) : 0,
                completedToday: this.history.length,
                readyWaiting: active.filter((t) => t.status === 'ready').length,
            };
        },

        /* ---------------------------------------------------------------
           Ticket lifecycle
           --------------------------------------------------------------- */
        async acceptTicket(t) {
            const data = await this.request(routes.orders + '/' + t.orderId + '/status', { status: 'accepted' });
            if (data) this.notify('KOT #' + t.kot + ' accepted');
        },
        async startPreparing(t) {
            const data = await this.request(routes.orders + '/' + t.orderId + '/status', { status: 'preparing' });
            if (data) this.notify('KOT #' + t.kot + ' - preparation started');
        },
        async markItemReady(t, i) {
            const data = await this.request(routes.items + '/' + (i.id || i.uid) + '/status', { status: 'ready' });
            if (data) this.notify(i.name + ' ready', 'success');
        },
        async markAllReady(t) {
            const data = await this.request(routes.orders + '/' + t.orderId + '/status', { status: 'ready' });
            if (data) this.notify('KOT #' + t.kot + ' ready', 'success');
        },
        /** A ticket becomes READY once every non-cancelled, non-held, available item is ready. */
        maybePromoteReady(t) {
            const resolvable = t.items.filter((i) => i.status !== 'cancelled' && i.status !== 'unavailable' && i.fire !== 'hold');
            if (resolvable.length && resolvable.every((i) => i.status === 'ready') && t.status !== 'ready') {
                t.status = 'ready';
                t.readyAt = this.now;
                this.notify(`KOT #${t.kot} ready`, 'success');
            }
        },
        notifyWaiter(t) {
            t.waiterNotified = true;
            this.notify(`${t.waiter || 'Front of house'} notified — ${this.orderLabel(t)} ready`);
        },
        async markPickedUp(t) {
            const data = await this.request(routes.orders + '/' + t.orderId + '/status', { status: 'served' });
            if (!data) return;
            this.history.unshift({ ...t, status: 'picked_up', pickedUpAt: this.now });
            if (this.activeKot === t.kot) { this.activeKot = null; this.closeAll(); }
            this.notify('KOT #' + t.kot + ' picked up');
        },
        prepMinutes(t) {
            return t.startedAt && t.readyAt ? Math.max(0, Math.round((t.readyAt - t.startedAt) / 60000)) : null;
        },

        /** Fire a held course — its items rejoin normal preparation tracking. */
        fireCourse(t, course) {
            t.items.filter((i) => i.course === course && i.fire === 'hold').forEach((i) => (i.fire = 'fire'));
            this.notify(`${course} course fired for ${this.orderLabel(t)}`);
        },

        /* ---------------------------------------------------------------
           Detail drawer
           --------------------------------------------------------------- */
        openDetail(t) {
            this.activeKot = t.kot;
            this.open('detail');
        },

        /* ---------------------------------------------------------------
           Priority
           --------------------------------------------------------------- */
        openPriority(t) {
            this.priorityDraft = { kot: t.kot, value: t.priority };
            this.swap('priority');
        },
        confirmPriority() {
            const t = this.ticket(this.priorityDraft.kot);
            if (t) t.priority = this.priorityDraft.value;
            this.back();
            this.notify('Priority updated');
        },

        /* ---------------------------------------------------------------
           Reprint
           --------------------------------------------------------------- */
        openReprint(t) {
            this.reprintDraft = { kot: t.kot, reason: '' };
            this.swap('reprint');
        },
        confirmReprint() {
            // backend: send the reprint job to the station printer
            this.notify(`KOT #${this.reprintDraft.kot} reprinted`);
            this.back();
        },

        /* ---------------------------------------------------------------
           Cancellation acknowledgement + item unavailable
           --------------------------------------------------------------- */
        acknowledgeCancel(t, i) {
            i.status = 'cancelled';
            this.maybePromoteReady(t);
            this.notify(`Cancellation on ${i.name} acknowledged`, 'warn');
        },
        openUnavailable(t, i) {
            this.unavailableDraft = { kot: t.kot, uid: i.uid, reason: '' };
            this.swap('unavailable');
        },
        confirmUnavailable() {
            const t = this.ticket(this.unavailableDraft.kot);
            const i = t?.items.find((x) => x.uid === this.unavailableDraft.uid);
            if (i) {
                i.status = 'unavailable';
                i.unavailableReason = this.unavailableDraft.reason;
            }
            if (t) this.maybePromoteReady(t);
            this.back();
            // backend: this is what would notify POS + the assigned waiter
            this.notify('Marked unavailable — POS and waiter notified', 'warn');
        },

        /* ---------------------------------------------------------------
           History
           --------------------------------------------------------------- */
        openHistory() {
            this.open('history');
        },
        get filteredHistory() {
            const q = this.historyQuery.trim().toLowerCase();
            if (!q) return this.history;
            return this.history.filter((t) => [t.table, t.token, t.orderCode, 'kot' + t.kot].filter(Boolean).join(' ').toLowerCase().includes(q));
        },

        /* ---------------------------------------------------------------
           Expeditor view — group active tickets by order across stations
           --------------------------------------------------------------- */
        get expeditorGroups() {
            const groups = new Map();
            this.filteredTickets.forEach((t) => {
                if (!groups.has(t.orderCode)) {
                    groups.set(t.orderCode, { orderCode: t.orderCode, orderType: t.orderType, table: t.table, token: t.token, waiter: t.waiter, guests: t.guests, placedAt: t.placedAt, tickets: [] });
                }
                const g = groups.get(t.orderCode);
                g.placedAt = Math.min(g.placedAt, t.placedAt);
                g.tickets.push(t);
            });
            return [...groups.values()]
                .map((g) => {
                    const items = g.tickets.flatMap((t) => t.items).filter((i) => i.status !== 'cancelled');
                    const stationMap = {};
                    items.forEach((i) => {
                        (stationMap[i.station] ||= { ready: 0, total: 0 });
                        stationMap[i.station].total++;
                        if (i.status === 'ready') stationMap[i.station].ready++;
                    });
                    const readyCount = items.filter((i) => i.status === 'ready').length;
                    return {
                        ...g,
                        items,
                        stationBreakdown: Object.entries(stationMap).map(([key, v]) => ({ key, label: this.stationLabel(key), ...v })),
                        readyCount,
                        totalCount: items.length,
                        allReady: items.length > 0 && readyCount === items.length,
                    };
                })
                .sort((a, b) => a.placedAt - b.placedAt);
        },
        markGroupReadyForService(g) {
            g.tickets.forEach((t) => { if (t.status !== 'ready') this.markAllReady(t); });
            this.notify(`${g.orderCode} marked ready for service`, 'success');
        },

        /* ---------------------------------------------------------------
           Full screen (kitchen TV displays)
           --------------------------------------------------------------- */
        toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen?.().catch(() => {});
                this.isFullscreen = true;
            } else {
                document.exitFullscreen?.().catch(() => {});
                this.isFullscreen = false;
            }
        },

        /* ---------------------------------------------------------------
           Connection / printer — click to preview both states in this demo
           --------------------------------------------------------------- */
        toggleConnection() {
            this.connected = !this.connected;
        },
        togglePrinter() {
            this.printerReady = !this.printerReady;
        },

        /* ---------------------------------------------------------------
           New-KOT alert queue
           --------------------------------------------------------------- */
        pushAlert(t) {
            const id = `${t.kot}-${this.notifications.length}`;
            this.notifications.push({ id, kot: t.kot, label: this.orderLabel(t), count: t.items.length });
            // backend: play a chime here when soundOn && soundModes.newKot
            setTimeout(() => this.dismissAlert(id), 6000);
        },
        dismissAlert(id) {
            this.notifications = this.notifications.filter((n) => n.id !== id);
        },
    };
}

