import {
    CANCEL_REASONS,
    CATEGORIES,
    CHARGE_CONFIG,
    CUSTOMERS,
    DISCOUNT_REASONS,
    FLOORS,
    KITCHEN_LOAD,
    KOT_HISTORY,
    MENU,
    MODIFIER_GROUPS,
    OPERATOR,
    PAYMENT_METHODS,
    READY_ALERTS,
    RECENT_IDS,
    RUNNING_ORDERS,
    SEED_CART,
    SHORTCUTS,
    TABLES,
    VENUE,
    WAITERS,
} from './demo-data.js';
import { subscribeRealtime } from '../shared/realtime.js';

const inr = (n, decimals = 0) =>
    '₹' +
    Number(n || 0).toLocaleString('en-IN', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    });
const boot = window.posModule || {};
const routes = window.posRoutes || {};
const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

/**
 * Alpine root for the POS terminal.
 *
 * Everything here is presentation state. No persistence, no network, no KOT
 * dispatch, no payment capture — those are backend concerns and are marked
 * with `// backend:` where the seam will be.
 */
export default function posApp() {
    return {
        /* ---------------------------------------------------------------
           Reference data
           --------------------------------------------------------------- */
        venue: boot.venue || VENUE,
        operator: boot.operator || OPERATOR,
        categories: boot.categories || CATEGORIES,
        menu: boot.menu || MENU,
        modifierGroups: MODIFIER_GROUPS,
        floors: FLOORS,
        tables: TABLES,
        customers: boot.customers || CUSTOMERS,
        runningOrders: boot.runningOrders || RUNNING_ORDERS,
        kotHistory: KOT_HISTORY,
        kitchen: KITCHEN_LOAD,
        waiters: WAITERS,
        discountReasons: DISCOUNT_REASONS,
        cancelReasons: CANCEL_REASONS,
        paymentMethods: PAYMENT_METHODS,
        charges: CHARGE_CONFIG,
        shortcuts: SHORTCUTS,

        /* ---------------------------------------------------------------
           Order state
           --------------------------------------------------------------- */
        orderType: 'dinein', // dinein | takeaway | delivery
        order: {
            id: boot.activeOrder?.id || null,
            code: 'ORD-1028',
            table: 'T08',
            floor: 'Ground Floor',
            guests: 4,
            waiter: 'Rahul',
            customer: null,
            openedAt: null,
            // takeaway
            token: '106',
            pickupAt: '20:15',
            // delivery
            address: '',
            deliveryNotes: '',
            deliveryMode: 'own', // own | aggregator
            aggregator: '',
            notes: '',
        },
        cart: (boot.activeOrder?.items || []).map((l) => ({ ...l })),
        nextUid: 200,
        nextKot: 1046,
        round: 3,
        discount: null, // { mode:'pct'|'amt', value, reason, scope:'bill'|'item', target, approvedBy }
        payments: [],
        held: false,

        /* ---------------------------------------------------------------
           UI state
           --------------------------------------------------------------- */
        activeCat: 'all',
        dietFilter: 'all', // all | veg | nonveg | egg
        availableOnly: false,
        query: '',
        clock: '',
        duration: 18,
        stack: [], // overlay stack; last entry is the visible dialog
        moreOpen: false,
        alerts: [],
        toast: null,
        saving: false,

        // Per-dialog drafts
        tableFloor: 'ground',
        tableQuery: '',
        tableAvailableOnly: false,
        customerQuery: '',
        customerDraft: { name: '', phone: '' },
        customerCreating: false,
        config: null,
        discountDraft: { mode: 'pct', value: '', reason: '', scope: 'bill', target: null },
        cancelDraft: { uid: null, reason: '', note: '' },
        approval: { title: '', detail: '', pin: '', error: '', resolve: null },
        payDraft: { method: 'cash', amount: '', reference: '' },
        split: { mode: 'equal', ways: 4, assign: {}, activeBill: 1, bills: 2, amounts: [] },
        kotFilter: 'all',

        /* ---------------------------------------------------------------
           Lifecycle
           --------------------------------------------------------------- */
        init() {
            if (!this.cart.length) this.cart = [];
            if (boot.activeOrder) {
                this.order.id = boot.activeOrder.id;
                this.order.code = boot.activeOrder.code;
                this.order.table = boot.activeOrder.table || this.order.table;
                this.order.guests = boot.activeOrder.guests || this.order.guests;
                this.order.waiter = boot.activeOrder.waiter || this.order.waiter;
                this.order.customer = boot.activeOrder.customer || null;
                this.order.token = boot.activeOrder.token || this.order.token;
                this.orderType = boot.activeOrder.type || this.orderType;
            }
            this.order.openedAt = Date.now() - 18 * 60000;
            this.alerts = READY_ALERTS.map((a) => ({ ...a }));
            this.tick();
            setInterval(() => this.tick(), 20000);
            this._unsubscribeRealtime = subscribeRealtime(['pos', 'orders', 'tables', 'reservations', 'inventory', 'menu'], () => this.refreshFromServer());
        },
        async refreshFromServer() {
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
            const now = new Date();
            this.clock = now.toLocaleTimeString('en-IN', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false,
            });
            this.duration = Math.max(0, Math.round((Date.now() - this.order.openedAt) / 60000));
        },

        /* ---------------------------------------------------------------
           Overlay stack — one dialog visible at a time, Esc pops one level
           --------------------------------------------------------------- */
        get overlay() {
            return this.stack.length ? this.stack[this.stack.length - 1] : null;
        },
        isOpen(name) {
            return this.overlay === name;
        },
        anyOpen() {
            return this.stack.length > 0;
        },
        open(name) {
            this.moreOpen = false;
            if (this.overlay === name) return;
            this.stack.push(name);
            this.$nextTick(() => this.focusFirst());
        },
        /** Replace the top dialog, keeping the level below reachable via back(). */
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
        applyServerState(data) {
            if (!data) return;
            if (data.venue) this.venue = data.venue;
            if (data.operator) this.operator = data.operator;
            if (data.categories) this.categories = data.categories;
            if (data.menu) this.menu = data.menu;
            if (data.customers) this.customers = data.customers;
            if (data.runningOrders) this.runningOrders = data.runningOrders;
            if (data.activeOrder) {
                this.order.id = data.activeOrder.id;
                this.order.code = data.activeOrder.code;
                this.order.table = data.activeOrder.table || this.order.table;
                this.order.guests = data.activeOrder.guests || this.order.guests;
                this.order.waiter = data.activeOrder.waiter || this.order.waiter;
                this.order.customer = data.activeOrder.customer || null;
                this.order.token = data.activeOrder.token || this.order.token;
                this.orderType = data.activeOrder.type || this.orderType;
                this.cart = (data.activeOrder.items || []).map((l) => ({ ...l }));
            }
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
                    this.notify(first || data.message || 'POS update failed', 'warn');
                    return null;
                }
                this.applyServerState(data);
                return data;
            } catch (error) {
                this.notify('Network error while updating POS', 'warn');
                return null;
            } finally {
                this.saving = false;
            }
        },

        /* ---------------------------------------------------------------
           Menu filtering
           --------------------------------------------------------------- */
        get visibleMenu() {
            const q = this.query.trim().toLowerCase();
            return this.menu.filter((m) => {
                if (this.activeCat === 'favorites' && !m.fav) return false;
                if (this.activeCat === 'recent' && !RECENT_IDS.includes(m.id)) return false;
                if (!['all', 'favorites', 'recent'].includes(this.activeCat) && m.cat !== this.activeCat) return false;
                if (this.dietFilter !== 'all' && m.diet !== this.dietFilter) return false;
                if (this.availableOnly && m.stock === 'out') return false;
                if (q && !(m.name.toLowerCase().includes(q) || m.code.toLowerCase().includes(q))) return false;
                return true;
            });
        },
        categoryCount(key) {
            if (key === 'all') return this.menu.length;
            if (key === 'favorites') return this.menu.filter((m) => m.fav).length;
            if (key === 'recent') return RECENT_IDS.length;
            return this.menu.filter((m) => m.cat === key).length;
        },
        clearFilters() {
            this.query = '';
            this.dietFilter = 'all';
            this.availableOnly = false;
            this.activeCat = 'all';
        },

        /* ---------------------------------------------------------------
           Adding items
           --------------------------------------------------------------- */
        needsConfig(item) {
            return (item.mods || []).some((g) => this.modifierGroups[g]?.required);
        },
        hasOptions(item) {
            return (item.mods || []).length > 0;
        },

        tapItem(item) {
            if (item.stock === 'out') return;
            if (this.needsConfig(item)) return this.openConfig(item);
            this.pushLine(item, 1, null, [], '');
        },

        /** Right-click / long-press any tile to reach notes + optional modifiers. */
        openConfig(item, existingUid = null) {
            const groups = (item.mods || []).map((g) => this.modifierGroups[g]).filter(Boolean);
            const selections = {};
            groups.forEach((g) => {
                selections[g.id] = g.type === 'multi' ? [] : g.required ? g.options[0].id : null;
            });

            let qty = 1;
            let note = '';
            if (existingUid) {
                const line = this.cart.find((c) => c.uid === existingUid);
                if (line) {
                    qty = line.qty;
                    note = line.note;
                }
            }

            this.config = { item, groups, selections, qty, note, editingUid: existingUid };
            this.open('config');
        },

        configPick(group, optionId) {
            if (group.type === 'multi') {
                const list = this.config.selections[group.id];
                const i = list.indexOf(optionId);
                i === -1 ? list.push(optionId) : list.splice(i, 1);
            } else {
                this.config.selections[group.id] = this.config.selections[group.id] === optionId && !group.required ? null : optionId;
            }
        },
        configChecked(group, optionId) {
            const v = this.config.selections[group.id];
            return group.type === 'multi' ? v.includes(optionId) : v === optionId;
        },
        get configModifiers() {
            if (!this.config) return [];
            const out = [];
            this.config.groups.forEach((g) => {
                const v = this.config.selections[g.id];
                const ids = g.type === 'multi' ? v : v ? [v] : [];
                ids.forEach((id) => {
                    const opt = g.options.find((o) => o.id === id);
                    if (opt) out.push({ group: g.id, label: opt.label, delta: opt.delta, primary: g.required && g.type === 'single' });
                });
            });
            return out;
        },
        get configUnitPrice() {
            if (!this.config) return 0;
            return this.config.item.price + this.configModifiers.reduce((s, m) => s + m.delta, 0);
        },
        get configTotal() {
            return this.configUnitPrice * (this.config?.qty || 0);
        },
        get configValid() {
            if (!this.config) return false;
            return this.config.groups.every((g) => {
                if (!g.required) return true;
                const v = this.config.selections[g.id];
                return g.type === 'multi' ? v.length > 0 : !!v;
            });
        },
        commitConfig() {
            if (!this.configValid) return;
            const mods = this.configModifiers;
            const variant = mods.find((m) => m.primary)?.label || null;
            const extras = mods.filter((m) => !m.primary).map((m) => ({ label: m.label, delta: m.delta }));

            if (this.config.editingUid) {
                const line = this.cart.find((c) => c.uid === this.config.editingUid);
                if (line) {
                    line.qty = this.config.qty;
                    line.note = this.config.note;
                    line.variant = variant;
                    line.modifiers = extras;
                }
            } else {
                this.pushLine(this.config.item, this.config.qty, variant, extras, this.config.note);
            }
            this.config = null;
            this.back();
        },

        pushLine(item, qty, variant, modifiers, note) {
            // Identical unsent lines merge; anything customised stays separate.
            const signature = JSON.stringify([item.id, variant, modifiers.map((m) => m.label).sort(), note]);
            const twin = this.cart.find(
                (c) => c.status === 'unsent' && JSON.stringify([c.ref, c.variant || null, c.modifiers.map((m) => m.label).sort(), c.note]) === signature
            );
            if (twin) {
                twin.qty += qty;
            } else {
                this.cart.push({
                    uid: this.nextUid++,
                    ref: item.id,
                    name: item.name,
                    price: item.price,
                    qty,
                    diet: item.diet,
                    station: item.station,
                    variant: variant || null,
                    modifiers,
                    note: note || '',
                    status: 'unsent',
                    kot: null,
                    sentAt: null,
                });
            }
            this.scrollCartToUnsent();
        },

        scrollCartToUnsent() {
            this.$nextTick(() => {
                const el = this.$refs.cartScroll;
                if (el) el.scrollTop = 0;
            });
        },

        /* ---------------------------------------------------------------
           Cart line operations
           --------------------------------------------------------------- */
        line(uid) {
            return this.cart.find((c) => c.uid === uid);
        },
        unitPrice(l) {
            return l.price + (l.modifiers || []).reduce((s, m) => s + m.delta, 0);
        },
        lineTotal(l) {
            return this.unitPrice(l) * l.qty;
        },
        editable(l) {
            return l.status === 'unsent';
        },

        bump(uid, delta) {
            const l = this.line(uid);
            if (!l || !this.editable(l)) return;
            const next = l.qty + delta;
            if (next <= 0) return this.removeLine(uid);
            l.qty = next;
        },
        /** Sent lines can only grow, and the increment goes out as a new line. */
        addMore(l) {
            const item = this.menu.find((m) => m.id === l.ref);
            if (!item) return;
            this.pushLine(item, 1, l.variant, [...l.modifiers], l.note);
            this.notify(`${l.name} added to the new round`);
        },
        removeLine(uid) {
            const i = this.cart.findIndex((c) => c.uid === uid);
            if (i === -1) return;
            if (this.cart[i].status !== 'unsent') return; // never silently drop a dispatched line
            this.cart.splice(i, 1);
        },
        editLine(l) {
            const item = this.menu.find((m) => m.id === l.ref);
            if (!item) return;
            this.openConfig(item, l.uid);
        },

        askCancel(l) {
            this.cancelDraft = { uid: l.uid, reason: '', note: '' };
            this.open('cancel');
        },
        async confirmCancel() {
            if (!this.cancelDraft.reason) return;
            const l = this.line(this.cancelDraft.uid);
            if (l) {
                if (l.id && l.status !== 'unsent') {
                    const data = await this.api(`${routes.itemCancel}/${l.id}/cancel`, {
                        method: 'PATCH',
                        body: JSON.stringify({ reason: this.cancelDraft.reason }),
                    });
                    if (!data) return;
                    this.back();
                    this.notify('Item cancelled - stock reversed if applicable', 'warn');
                    return;
                }
                // backend: void the KOT line at the station printer + audit trail
                l.status = 'cancelled';
                l.cancelReason = this.cancelDraft.note
                    ? `${this.cancelDraft.reason} — ${this.cancelDraft.note}`
                    : this.cancelDraft.reason;
            }
            this.back();
            this.notify('Item cancelled — kitchen notified', 'warn');
        },

        async moveToBilling() {
            if (this.unsentLines.length) {
                this.notify('Send all new items to KOT before billing.', 'warn');
                return;
            }
            if (!this.order.id) {
                this.notify('Create the order by sending KOT first.', 'warn');
                return;
            }

            const data = await this.api(`${routes.billOrder}/${this.order.id}/billing`, {
                method: 'POST',
                body: JSON.stringify({}),
            });

            if (data?.redirect) {
                window.location.assign(data.redirect);
            }
        },

        /* ---------------------------------------------------------------
           Cart grouping: unsent round pinned first, dispatched rounds after
           --------------------------------------------------------------- */
        get unsentLines() {
            return this.cart.filter((c) => c.status === 'unsent');
        },
        get sentRounds() {
            const map = new Map();
            this.cart
                .filter((c) => c.status !== 'unsent')
                .forEach((c) => {
                    if (!map.has(c.kot)) map.set(c.kot, { kot: c.kot, sentAt: c.sentAt, lines: [] });
                    map.get(c.kot).lines.push(c);
                });
            return [...map.values()].sort((a, b) => b.kot - a.kot);
        },
        get unsentCount() {
            return this.unsentLines.reduce((s, c) => s + c.qty, 0);
        },
        get itemCount() {
            return this.cart.filter((c) => c.status !== 'cancelled').reduce((s, c) => s + c.qty, 0);
        },
        get readyCount() {
            return this.cart.filter((c) => c.status === 'ready').length;
        },

        statusLabel(s) {
            return { unsent: 'New', sent: 'Sent', accepted: 'Accepted', preparing: 'Preparing', ready: 'Ready', served: 'Served', cancelled: 'Cancelled' }[s] || s;
        },
        /** Text + glyph + border weight, so status never depends on hue alone. */
        statusGlyph(s) {
            return { unsent: '＋', sent: '→', accepted: '✓', preparing: '◑', ready: '●', served: '✓✓', cancelled: '✕' }[s] || '•';
        },
        statusClass(s) {
            return {
                unsent: 'border-amber-400 bg-amber-50 text-amber-800',
                sent: 'border-sky-300 bg-sky-50 text-sky-800',
                accepted: 'border-indigo-300 bg-indigo-50 text-indigo-800',
                preparing: 'border-orange-300 bg-orange-50 text-orange-800',
                ready: 'border-emerald-400 bg-emerald-50 text-emerald-800',
                served: 'border-slate-300 bg-slate-100 text-slate-600',
                cancelled: 'border-rose-300 bg-rose-50 text-rose-700 line-through',
            }[s] || 'border-slate-300 bg-slate-100 text-slate-600';
        },

        /* ---------------------------------------------------------------
           KOT dispatch
           --------------------------------------------------------------- */
        async sendKot() {
            if (!this.unsentLines.length) return;
            const data = await this.api(routes.kot, {
                method: 'POST',
                body: JSON.stringify({
                    orderId: this.order.id,
                    orderType: this.orderType,
                    table: this.order.table,
                    guests: this.order.guests,
                    customerId: Number.isInteger(Number(this.order.customer?.id)) ? Number(this.order.customer.id) : null,
                    token: this.order.token,
                    items: this.unsentLines.map((l) => ({
                        menuItemId: l.ref,
                        qty: l.qty,
                        variant: l.variant,
                        modifiers: l.modifiers || [],
                        note: l.note,
                    })),
                }),
            });
            if (data) this.notify(data.message || 'KOT sent', 'success');
            return;
            const kot = this.nextKot++;
            const at = this.clock;
            const lines = this.unsentLines.map((l) => ({
                name: l.name,
                qty: l.qty,
                note: [l.variant, ...l.modifiers.map((m) => m.label), l.note].filter(Boolean).join(' · '),
                state: 'SENT',
            }));
            this.unsentLines.forEach((l) => {
                // backend: dispatch to station printers / KDS, then persist
                l.status = 'sent';
                l.kot = kot;
                l.sentAt = at;
            });
            this.kotHistory.push({ kot, round: this.round++, sentAt: at, by: this.operator.name, printer: 'Main Kitchen + Tandoor', lines });
            this.kitchen.new += 1;
            this.notify(`KOT #${kot} sent — ${lines.length} line(s)`, 'success');
        },

        /* ---------------------------------------------------------------
           Money
           --------------------------------------------------------------- */
        get billableLines() {
            return this.cart.filter((c) => c.status !== 'cancelled');
        },
        get subtotal() {
            return this.billableLines.reduce((s, c) => s + this.lineTotal(c), 0);
        },
        get discountAmount() {
            if (!this.discount) return 0;
            const base = this.discount.scope === 'item' && this.discount.target
                ? this.lineTotal(this.line(this.discount.target) || { price: 0, qty: 0, modifiers: [] })
                : this.subtotal;
            const raw = this.discount.mode === 'pct' ? (base * this.discount.value) / 100 : this.discount.value;
            return Math.min(Math.round(raw), this.subtotal);
        },
        get taxableBase() {
            return Math.max(0, this.subtotal - this.discountAmount);
        },
        get taxAmount() {
            return Math.round(this.taxableBase * this.charges.taxRate);
        },
        get serviceAmount() {
            return this.charges.serviceEnabled ? Math.round(this.taxableBase * this.charges.serviceRate) : 0;
        },
        get grossTotal() {
            return this.taxableBase + this.taxAmount + this.serviceAmount;
        },
        get roundOff() {
            return Math.round(this.grossTotal) - this.grossTotal;
        },
        get total() {
            return Math.round(this.grossTotal);
        },
        get paid() {
            return this.payments.reduce((s, p) => s + p.amount, 0);
        },
        get due() {
            return Math.max(0, this.total - this.paid);
        },
        get changeDue() {
            return Math.max(0, this.paid - this.total);
        },
        money: (n) => inr(n, 0),
        money2: (n) => inr(n, 2),

        /* ---------------------------------------------------------------
           Discount + manager approval
           --------------------------------------------------------------- */
        openDiscount() {
            this.discountDraft = this.discount
                ? { ...this.discount }
                : { mode: 'pct', value: '', reason: '', scope: 'bill', target: null };
            this.open('discount');
        },
        get discountPreview() {
            const v = Number(this.discountDraft.value) || 0;
            const base = this.subtotal;
            const raw = this.discountDraft.mode === 'pct' ? (base * v) / 100 : v;
            return Math.min(Math.round(raw), base);
        },
        get discountPct() {
            return this.subtotal ? (this.discountPreview / this.subtotal) * 100 : 0;
        },
        get discountNeedsApproval() {
            return this.discountPct > this.operator.discountLimitPct;
        },
        applyDiscount() {
            const v = Number(this.discountDraft.value) || 0;
            if (v <= 0 || !this.discountDraft.reason) return;

            const commit = (approvedBy = null) => {
                this.discount = { ...this.discountDraft, value: v, approvedBy };
                this.back();
                this.notify(`Discount applied — ${this.money(this.discountAmount)}`, 'success');
            };

            if (this.discountNeedsApproval) {
                this.requestApproval({
                    title: 'Manager approval required',
                    detail: `${Math.round(this.discountPct)}% discount exceeds the ${this.operator.discountLimitPct}% limit for ${this.operator.role.toLowerCase()} ${this.operator.name}.`,
                    onApprove: () => commit('Manager PIN'),
                });
                return;
            }
            commit();
        },
        clearDiscount() {
            this.discount = null;
            this.notify('Discount removed');
        },

        requestApproval({ title, detail, onApprove }) {
            this.approval = { title, detail, pin: '', error: '', resolve: onApprove };
            this.open('approval');
        },
        submitApproval() {
            // backend: verify the manager PIN server-side and log the override
            if (this.approval.pin.length < 4) {
                this.approval.error = 'Enter the 4-digit manager PIN.';
                return;
            }
            const run = this.approval.resolve;
            this.back(); // dismiss approval, revealing the dialog underneath
            if (run) run();
        },

        /* ---------------------------------------------------------------
           Table / customer / waiter pickers
           --------------------------------------------------------------- */
        get visibleTables() {
            const q = this.tableQuery.trim().toLowerCase();
            return this.tables.filter((t) => {
                if (t.floor !== this.tableFloor) return false;
                if (this.tableAvailableOnly && t.status !== 'available') return false;
                if (q && !t.id.toLowerCase().includes(q)) return false;
                return true;
            });
        },
        floorLabel(key) {
            return this.floors.find((f) => f.key === key)?.label || '';
        },
        tableStatusClass(s) {
            return {
                available: 'border-emerald-300 bg-emerald-50 hover:border-emerald-500',
                reserved: 'border-violet-300 bg-violet-50 hover:border-violet-500',
                occupied: 'border-sky-300 bg-sky-50 hover:border-sky-500',
                billing: 'border-amber-400 bg-amber-50 hover:border-amber-500',
                cleaning: 'border-slate-300 bg-slate-100 hover:border-slate-400 pos-hatch',
            }[s];
        },
        pickTable(t) {
            if (t.status === 'cleaning') return;
            this.order.table = t.id;
            this.order.floor = this.floorLabel(t.floor);
            if (t.guests) this.order.guests = t.guests;
            this.back();
            this.notify(`Table ${t.id} · ${this.order.floor}`);
        },

        get visibleCustomers() {
            const q = this.customerQuery.trim().toLowerCase();
            if (!q) return this.customers.slice(0, 4);
            return this.customers.filter((c) => c.name.toLowerCase().includes(q) || c.phone.includes(q));
        },
        pickCustomer(c) {
            this.order.customer = c;
            this.back();
            this.notify(`${c.name} attached to ${this.order.code}`);
        },
        quickAddCustomer() {
            const { name, phone } = this.customerDraft;
            if (!name.trim() || phone.trim().length < 10) return;
            // backend: create the CRM record
            const c = { id: 'NEW', name: name.trim(), phone: phone.trim(), visits: 0, spend: 0, points: 0, tag: 'New' };
            this.customers.unshift(c);
            this.customerDraft = { name: '', phone: '' };
            this.customerCreating = false;
            this.pickCustomer(c);
        },
        detachCustomer() {
            this.order.customer = null;
        },

        /* ---------------------------------------------------------------
           Payment
           --------------------------------------------------------------- */
        openPayment() {
            this.moveToBilling();
        },
        quickCash(v) {
            this.payDraft.amount = String(v);
        },
        get cashChange() {
            const a = Number(this.payDraft.amount) || 0;
            return Math.max(0, a - this.due);
        },
        addPayment() {
            const amount = Number(this.payDraft.amount) || 0;
            if (amount <= 0) return;
            // Overpayment is only meaningful for cash (it becomes change).
            const captured = this.payDraft.method === 'cash' ? Math.min(amount, this.due) : Math.min(amount, this.due);
            this.payments.push({
                method: this.payDraft.method,
                label: this.paymentMethods.find((m) => m.key === this.payDraft.method)?.label,
                amount: captured,
                reference: this.payDraft.reference,
                tendered: amount,
            });
            this.payDraft = { method: 'cash', amount: String(this.due || ''), reference: '' };
        },
        removePayment(i) {
            this.payments.splice(i, 1);
        },
        settle() {
            if (this.due > 0) return;
            // backend: persist settlement, close the table, print the final bill
            this.notify(`${this.order.code} settled — ${this.money(this.total)}`, 'success');
            this.closeAll();
        },

        /* ---------------------------------------------------------------
           Split bill
           --------------------------------------------------------------- */
        openSplit() {
            this.split = {
                mode: 'equal',
                ways: this.order.guests || 2,
                assign: Object.fromEntries(this.billableLines.map((l) => [l.uid, null])),
                activeBill: 1,
                bills: 2,
                amounts: [Math.round(this.total / 2), this.total - Math.round(this.total / 2)],
            };
            this.open('split');
        },
        get splitEach() {
            return this.split.ways > 0 ? Math.round(this.total / this.split.ways) : 0;
        },
        assignLine(uid) {
            // A line belongs to exactly one bill; clicking the active bill again unassigns.
            this.split.assign[uid] = this.split.assign[uid] === this.split.activeBill ? null : this.split.activeBill;
        },
        splitBillTotal(n) {
            return this.billableLines
                .filter((l) => this.split.assign[l.uid] === n)
                .reduce((s, l) => s + this.lineTotal(l), 0);
        },
        get splitUnassigned() {
            return this.billableLines.filter((l) => !this.split.assign[l.uid]);
        },
        setSplitBills(n) {
            this.split.bills = n;
            Object.keys(this.split.assign).forEach((uid) => {
                if (this.split.assign[uid] > n) this.split.assign[uid] = null;
            });
            if (this.split.activeBill > n) this.split.activeBill = 1;
        },

        /* ---------------------------------------------------------------
           Secondary actions (design-level stubs)
           --------------------------------------------------------------- */
        moreActions: [
            { key: 'customer', label: 'Add / change customer', hint: 'F3' },
            { key: 'table', label: 'Change table', hint: 'F4' },
            { key: 'waiter', label: 'Change waiter' },
            { key: 'notes', label: 'Order notes' },
            { key: 'kot', label: 'View KOT history' },
            { key: 'billing', label: 'Send to billing' },
            { key: 'cancelOrder', label: 'Cancel order', danger: true },
        ],
        runMore(key) {
            this.moreOpen = false;
            switch (key) {
                case 'customer': return this.open('customer');
                case 'table': return this.open('table');
                case 'waiter': return this.open('waiter');
                case 'notes': return this.open('notes');
                case 'kot': return this.open('kot');
                case 'billing': return this.moveToBilling();
                case 'cancelOrder':
                    return this.requestApproval({
                        title: 'Cancel entire order?',
                        detail: `${this.order.code} has ${this.itemCount} item(s) worth ${this.money(this.total)}, with ${this.kotHistory.length} KOT round(s) already in the kitchen.`,
                        onApprove: () => this.notify('Order cancellation logged', 'warn'),
                    });
            }
        },

        hold() {
            this.held = !this.held;
            this.notify(this.held ? `${this.order.code} put on hold` : `${this.order.code} resumed`);
        },
        loadOrder(o) {
            this.back();
            this.notify(`Loaded ${o.code} — ${o.label}`);
        },
        dismissAlert(id) {
            this.alerts = this.alerts.filter((a) => a.id !== id);
        },
        markServed(alert) {
            this.cart.filter((c) => c.status === 'ready' && c.name.startsWith(alert.item.slice(0, 12))).forEach((c) => (c.status = 'served'));
            this.kitchen.ready = Math.max(0, this.kitchen.ready - 1);
            this.dismissAlert(alert.id);
        },

        /* ---------------------------------------------------------------
           Keyboard
           --------------------------------------------------------------- */
        hotkey(e) {
            const map = {
                F1: () => this.notify('New order — pick a table to begin'),
                F2: () => { this.closeAll(); this.$refs.search?.focus(); this.$refs.search?.select(); },
                F3: () => this.open('customer'),
                F4: () => this.open('table'),
                F6: () => this.sendKot(),
                F7: () => this.openDiscount(),
                F8: () => this.moveToBilling(),
                F9: () => this.openPayment(),
                F10: () => this.open('kot'),
            };
            if (map[e.key]) {
                e.preventDefault();
                map[e.key]();
                return;
            }
            if (e.key === 'Escape') {
                if (this.moreOpen) return (this.moreOpen = false);
                if (this.stack.length) return this.back();
                if (this.query) return (this.query = '');
                this.$refs.search?.blur();
                return;
            }
            if (e.key === '?' && !['INPUT', 'TEXTAREA'].includes(e.target.tagName)) {
                e.preventDefault();
                this.open('shortcuts');
            }
        },
    };
}
