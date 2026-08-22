import {
    BILL_DISCOUNT,
    CANCEL_REASONS,
    CHARGES,
    COMP_REASONS,
    CUSTOMER,
    CUSTOMERS,
    DISCOUNT_REASONS,
    INVOICE,
    ITEMS,
    OPERATOR,
    ORDER,
    PAYMENT_METHODS,
    PAYMENTS,
    REFUND_REASONS,
    SHORTCUTS,
    VALID_COUPONS,
    VENUE,
    VOID_REASONS,
    WAITERS,
} from './demo-data.js';

const inr = (n, decimals = 0) =>
    '₹' + Number(n || 0).toLocaleString('en-IN', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });

/**
 * Alpine root for Billing, Payment & Split Bill.
 *
 * Presentation state only, same seam pattern as the POS/Floor/KDS stores —
 * anywhere real settlement, printing, or gateway capture will eventually
 * land is marked `// backend:`. Money math is deliberately computed from
 * getters rather than cached, so every discount/comp/refund/payment edit
 * recalculates the whole chain live.
 */
export default function billingApp(posUrl, tablesUrl) {
    return {
        /* ---------------------------------------------------------------
           Reference data
           --------------------------------------------------------------- */
        posUrl,
        tablesUrl,
        venue: VENUE,
        operator: OPERATOR,
        invoice: { ...INVOICE, status: 'partially_paid', voided: false, voidReason: null },
        order: { ...ORDER },
        paymentMethods: PAYMENT_METHODS,
        discountReasons: DISCOUNT_REASONS,
        compReasons: COMP_REASONS,
        cancelReasons: CANCEL_REASONS,
        refundReasons: REFUND_REASONS,
        voidReasons: VOID_REASONS,
        waiters: WAITERS,
        customers: CUSTOMERS,
        shortcuts: SHORTCUTS,
        clock: '',

        /* ---------------------------------------------------------------
           Mutable dummy state
           --------------------------------------------------------------- */
        customer: { ...CUSTOMER, loyalty: { ...CUSTOMER.loyalty } },
        gstInvoice: false,
        items: ITEMS.map((i) => ({ ...i })),
        charges: { ...CHARGES },
        billDiscount: { ...BILL_DISCOUNT },
        loyaltyRedeemed: null, // { points, amount }
        coupon: null, // { code, pct, amount }
        payments: PAYMENTS.map((p) => ({ ...p })),
        refunds: [],

        /* ---------------------------------------------------------------
           UI state
           --------------------------------------------------------------- */
        stack: [],
        toast: null,
        moreOpen: false,
        printerReady: true,

        discountDraft: { scope: 'bill', mode: 'pct', value: '', reason: '', target: null },
        itemMenuOpenUid: null,
        compDraft: { uid: null, reason: '', note: '' },
        cancelDraft: { uid: null, reason: '' },
        payDraft: { method: 'cash', amount: '', reference: '', cardType: 'credit', last4: '' },
        upiStatus: 'waiting', // waiting | paid | failed
        customerQuery: '',
        customerDraft: { name: '', phone: '' },
        loyaltyDraft: { points: '' },
        couponDraft: { code: '' },
        refundDraft: { mode: 'full', method: 'cash', amount: '', reason: '', items: [] },
        voidDraft: { reason: '' },
        closeTableConfirm: false,

        split: {
            active: false,
            mode: 'equal', // equal | item | amount | guest
            ways: 3,
            assign: {}, // { uid: { billIdx: qty } }
            amounts: [],
            bills: [], // [{ idx, label, customer, total, status, payments }]
        },
        splitPay: { idx: null, method: 'cash', amount: '', reference: '' },

        approval: { title: '', detail: '', pin: '', error: '', resolve: null },

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
        orderDurationMinutes() {
            return this.order.startedMinutesAgo;
        },

        /* ---------------------------------------------------------------
           Overlay stack (same shape as POS / Floor / KDS)
           --------------------------------------------------------------- */
        get overlay() {
            return this.stack.length ? this.stack[this.stack.length - 1] : null;
        },
        open(name) {
            this.moreOpen = false;
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
           Money
           --------------------------------------------------------------- */
        money: (n) => inr(n, 0),
        money2: (n) => inr(n, 2),

        /** Non-cancelled, non-refunded lines — the only ones the payable chain touches. */
        get billableItems() {
            return this.items.filter((i) => i.status !== 'cancelled' && i.status !== 'refunded');
        },
        get cancelledItems() {
            return this.items.filter((i) => i.status === 'cancelled' || i.status === 'refunded');
        },
        get subtotal() {
            return this.billableItems.reduce((s, i) => s + i.amount, 0);
        },
        get itemDiscountTotal() {
            return this.items.filter((i) => i.status === 'discounted').reduce((s, i) => s + i.discount, 0);
        },
        get complimentaryTotal() {
            return this.items.filter((i) => i.status === 'complimentary').reduce((s, i) => s + i.amount, 0);
        },
        get billDiscountAmount() {
            if (!this.billDiscount) return 0;
            const base = this.subtotal - this.itemDiscountTotal - this.complimentaryTotal;
            const raw = this.billDiscount.mode === 'pct' ? (base * this.billDiscount.value) / 100 : this.billDiscount.value;
            return Math.min(Math.round(raw), base);
        },
        get loyaltyAmount() {
            return this.loyaltyRedeemed?.amount || 0;
        },
        get couponAmount() {
            return this.coupon?.amount || 0;
        },
        get totalDeductions() {
            return this.itemDiscountTotal + this.complimentaryTotal + this.billDiscountAmount + this.loyaltyAmount + this.couponAmount;
        },
        get taxableAmount() {
            return Math.max(0, this.subtotal - this.totalDeductions);
        },
        get cgstAmount() {
            return this.charges.taxMode === 'inclusive' ? 0 : Math.round(this.taxableAmount * this.charges.cgstRate * 100) / 100;
        },
        get sgstAmount() {
            return this.charges.taxMode === 'inclusive' ? 0 : Math.round(this.taxableAmount * this.charges.sgstRate * 100) / 100;
        },
        get serviceAmount() {
            return this.charges.serviceRemoved ? 0 : Math.round(this.taxableAmount * this.charges.serviceRate);
        },
        get grandTotalRaw() {
            return this.taxableAmount + this.cgstAmount + this.sgstAmount + this.serviceAmount;
        },
        get grandTotal() {
            return Math.round(this.grandTotalRaw);
        },
        get roundOff() {
            return this.grandTotal - this.grandTotalRaw;
        },

        /** Paid/due read from split bills once a split is active, otherwise from the main tender list. */
        get paidTotal() {
            if (this.split.active) return this.split.bills.reduce((s, b) => s + b.payments.reduce((s2, p) => s2 + p.amount, 0), 0);
            return this.payments.filter((p) => p.status === 'success').reduce((s, p) => s + p.amount, 0);
        },
        get dueAmount() {
            return Math.max(0, this.grandTotal - this.paidTotal);
        },
        get refundedTotal() {
            return this.refunds.reduce((s, r) => s + r.amount, 0);
        },

        get billStatus() {
            if (this.invoice.voided) return 'voided';
            if (this.refundedTotal > 0 && this.refundedTotal >= this.grandTotal) return 'refunded';
            if (this.refundedTotal > 0) return 'partially_refunded';
            if (this.dueAmount <= 0 && this.paidTotal > 0) return 'paid';
            if (this.paidTotal > 0) return 'partially_paid';
            return 'generated';
        },
        statusLabel(s) {
            return {
                draft: 'Draft', generated: 'Generated', partially_paid: 'Partially Paid', paid: 'Paid',
                voided: 'Voided', refunded: 'Refunded', partially_refunded: 'Partially Refunded',
            }[s] || s;
        },
        statusClass(s) {
            return {
                draft: 'border-slate-300 bg-slate-100 text-slate-600',
                generated: 'border-sky-300 bg-sky-50 text-sky-800',
                partially_paid: 'border-amber-400 bg-amber-50 text-amber-800',
                paid: 'border-emerald-400 bg-emerald-50 text-emerald-800',
                voided: 'border-rose-300 bg-rose-50 text-rose-700',
                refunded: 'border-rose-300 bg-rose-50 text-rose-700',
                partially_refunded: 'border-orange-300 bg-orange-50 text-orange-800',
            }[s] || 'border-slate-300 bg-slate-100 text-slate-600';
        },

        /* ---------------------------------------------------------------
           Item state helpers
           --------------------------------------------------------------- */
        netAmount(i) {
            if (i.status === 'cancelled' || i.status === 'refunded') return 0;
            if (i.status === 'complimentary') return 0;
            return i.amount - (i.discount || 0);
        },
        itemStatusLabel(s) {
            return { normal: 'Normal', complimentary: 'Complimentary', discounted: 'Discounted', cancelled: 'Cancelled', refunded: 'Refunded' }[s] || s;
        },
        itemStatusClass(s) {
            return {
                normal: 'border-transparent',
                complimentary: 'border-violet-300 bg-violet-50 text-violet-800',
                discounted: 'border-amber-300 bg-amber-50 text-amber-800',
                cancelled: 'border-rose-200 bg-rose-50 text-rose-500',
                refunded: 'border-rose-200 bg-rose-50 text-rose-500',
            }[s] || 'border-slate-200 bg-slate-50 text-slate-600';
        },
        toggleItemMenu(uid) {
            this.itemMenuOpenUid = this.itemMenuOpenUid === uid ? null : uid;
        },

        /* ---------------------------------------------------------------
           Item-level actions
           --------------------------------------------------------------- */
        openItemDiscount(i) {
            this.itemMenuOpenUid = null;
            this.discountDraft = { scope: 'item', mode: 'pct', value: '', reason: '', target: i.uid };
            this.open('discount');
        },
        openComplimentary(i) {
            this.itemMenuOpenUid = null;
            this.compDraft = { uid: i.uid, reason: '', note: '' };
            this.open('comp');
        },
        confirmComplimentary() {
            const i = this.items.find((x) => x.uid === this.compDraft.uid);
            if (!i || !this.compDraft.reason) return;
            const commit = (authorizedBy = this.operator.name) => {
                i.status = 'complimentary';
                i.compReason = this.compDraft.reason;
                i.compBy = authorizedBy;
                this.back();
                this.notify(`${i.name} marked complimentary`, 'success');
            };
            // backend: complimentary always needs a manager sign-off in the real system
            this.requestApproval({
                title: 'Manager approval required',
                detail: `Marking "${i.name}" (${this.money(i.amount)}) complimentary needs manager authorization.`,
                onApprove: () => commit('Manager PIN'),
            });
        },
        openCancelItem(i) {
            this.itemMenuOpenUid = null;
            this.cancelDraft = { uid: i.uid, reason: '' };
            this.open('cancelItem');
        },
        confirmCancelItem() {
            const i = this.items.find((x) => x.uid === this.cancelDraft.uid);
            if (!i || !this.cancelDraft.reason) return;
            i.status = 'cancelled';
            i.cancelReason = this.cancelDraft.reason;
            this.back();
            this.notify(`${i.name} cancelled — excluded from the bill`, 'warn');
        },
        viewKot(i) {
            this.itemMenuOpenUid = null;
            this.notify(`KOT #${i.kot} · ordered ${i.orderedAt}`);
        },
        viewOrderItem(i) {
            this.itemMenuOpenUid = null;
            this.notify(`${i.name} · ${i.qty} × ${this.money(i.rate)}`);
        },

        /* ---------------------------------------------------------------
           Discount (bill-level or item-level) + manager approval
           --------------------------------------------------------------- */
        openDiscount() {
            this.discountDraft = this.billDiscount ? { scope: 'bill', ...this.billDiscount } : { scope: 'bill', mode: 'pct', value: '', reason: '', target: null };
            this.open('discount');
        },
        get discountBase() {
            if (this.discountDraft.scope === 'item') {
                const i = this.items.find((x) => x.uid === this.discountDraft.target);
                return i ? i.amount : 0;
            }
            return this.subtotal - this.itemDiscountTotal - this.complimentaryTotal;
        },
        get discountPreview() {
            const v = Number(this.discountDraft.value) || 0;
            const raw = this.discountDraft.mode === 'pct' ? (this.discountBase * v) / 100 : v;
            return Math.min(Math.round(raw), this.discountBase);
        },
        get discountPct() {
            return this.discountBase ? (this.discountPreview / this.discountBase) * 100 : 0;
        },
        get discountNeedsApproval() {
            return this.discountPct > this.operator.discountLimitPct;
        },
        applyDiscount() {
            const v = Number(this.discountDraft.value) || 0;
            if (v <= 0 || !this.discountDraft.reason) return;

            const commit = (approvedBy = null) => {
                if (this.discountDraft.scope === 'item') {
                    const i = this.items.find((x) => x.uid === this.discountDraft.target);
                    if (i) { i.status = 'discounted'; i.discount = this.discountPreview; i.discountReason = this.discountDraft.reason; }
                } else {
                    this.billDiscount = { mode: this.discountDraft.mode, value: v, reason: this.discountDraft.reason, approvedBy };
                }
                this.back();
                this.notify(`Discount applied — ${this.money(this.discountPreview)}`, 'success');
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
        clearBillDiscount() {
            this.billDiscount = null;
            this.notify('Bill discount removed');
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
            this.back();
            if (run) run();
        },

        /* ---------------------------------------------------------------
           Service charge override
           --------------------------------------------------------------- */
        requestServiceOverride() {
            if (this.charges.serviceLocked) {
                this.requestApproval({
                    title: 'Manager approval required',
                    detail: `Service charge is locked for this terminal. Removing it needs manager authorization.`,
                    onApprove: () => { this.charges.serviceRemoved = !this.charges.serviceRemoved; this.notify(this.charges.serviceRemoved ? 'Service charge removed' : 'Service charge restored'); },
                });
                return;
            }
            this.charges.serviceRemoved = !this.charges.serviceRemoved;
            this.notify(this.charges.serviceRemoved ? 'Service charge removed' : 'Service charge restored');
        },

        /* ---------------------------------------------------------------
           Loyalty + coupon
           --------------------------------------------------------------- */
        openLoyalty() {
            this.loyaltyDraft = { points: '' };
            this.open('loyalty');
        },
        get loyaltyPreviewAmount() {
            const p = Number(this.loyaltyDraft.points) || 0;
            return Math.min(p, this.customer.loyalty.points) * this.customer.loyalty.valuePerPoint;
        },
        redeemLoyalty() {
            const p = Math.min(Number(this.loyaltyDraft.points) || 0, this.customer.loyalty.points);
            if (p <= 0) return;
            this.loyaltyRedeemed = { points: p, amount: p * this.customer.loyalty.valuePerPoint };
            this.customer.loyalty.points -= p;
            this.back();
            this.notify(`Redeemed ${p} points — ${this.money(this.loyaltyRedeemed.amount)}`, 'success');
        },
        clearLoyalty() {
            if (this.loyaltyRedeemed) this.customer.loyalty.points += this.loyaltyRedeemed.points;
            this.loyaltyRedeemed = null;
        },
        openCoupon() {
            this.couponDraft = { code: this.coupon?.code || '' };
            this.open('coupon');
        },
        applyCoupon() {
            const code = this.couponDraft.code.trim().toUpperCase();
            const pct = VALID_COUPONS[code];
            if (!pct) {
                this.notify(`"${code}" is invalid or expired`, 'warn');
                return;
            }
            const base = this.subtotal - this.itemDiscountTotal - this.complimentaryTotal - this.billDiscountAmount;
            this.coupon = { code, pct, amount: Math.round((base * pct) / 100) };
            this.back();
            this.notify(`Coupon ${code} applied — ${pct}% off`, 'success');
        },
        clearCoupon() {
            this.coupon = null;
        },

        /* ---------------------------------------------------------------
           Customer + GST invoice
           --------------------------------------------------------------- */
        get visibleCustomers() {
            const q = this.customerQuery.trim().toLowerCase();
            if (!q) return this.customers.slice(0, 4);
            return this.customers.filter((c) => c.name.toLowerCase().includes(q) || c.phone.includes(q));
        },
        pickCustomer(c) {
            this.customer = { ...c, email: this.customer.email, gstin: '', businessName: '', address: '', loyalty: { points: c.points, valuePerPoint: 1 } };
            this.back();
            this.notify(`${c.name} attached to ${this.invoice.code}`);
        },
        quickAddCustomer() {
            const { name, phone } = this.customerDraft;
            if (!name.trim() || phone.trim().length < 10) return;
            const c = { id: 'NEW', name: name.trim(), phone: phone.trim(), visits: 0, spend: 0, points: 0 };
            this.customers.unshift(c);
            this.customerDraft = { name: '', phone: '' };
            this.pickCustomer(c);
        },

        /* ---------------------------------------------------------------
           Payment
           --------------------------------------------------------------- */
        selectMethod(key) {
            this.payDraft.method = key;
            this.payDraft.amount = String(this.dueAmount || '');
            this.upiStatus = 'waiting';
        },
        quickCash(v) {
            this.payDraft.amount = String(v);
        },
        get cashChange() {
            const a = Number(this.payDraft.amount) || 0;
            return Math.max(0, a - this.dueAmount);
        },
        addPayment() {
            const amount = Number(this.payDraft.amount) || 0;
            if (amount <= 0 || this.dueAmount <= 0) return;
            const captured = Math.min(amount, this.dueAmount);
            const label = this.paymentMethods.find((m) => m.key === this.payDraft.method)?.label;
            let reference = this.payDraft.reference || null;
            if (['credit', 'debit'].includes(this.payDraft.method)) {
                reference = [this.payDraft.cardType === 'credit' ? 'Credit' : 'Debit', this.payDraft.last4 && `••${this.payDraft.last4}`, reference].filter(Boolean).join(' ');
            }
            this.payments.push({ method: this.payDraft.method, label, amount: captured, tendered: amount, reference, at: this.clock, status: 'success' });
            this.payDraft = { method: this.payDraft.method, amount: String(this.dueAmount - captured || ''), reference: '', cardType: 'credit', last4: '' };
            this.upiStatus = 'waiting';
        },
        removePayment(i) {
            this.payments.splice(i, 1);
        },
        quickFull(method) {
            this.selectMethod(method);
            this.payDraft.amount = String(this.dueAmount);
            this.addPayment();
        },
        markUpiReceived() {
            this.upiStatus = 'paid';
        },
        completePayment() {
            if (this.dueAmount > 0) return;
            // backend: persist settlement + trigger the printer job
            this.invoice.status = 'paid';
            this.open('success');
        },

        /* ---------------------------------------------------------------
           Split bill
           --------------------------------------------------------------- */
        openSplit() {
            if (!this.split.bills.length) this.computeSplit();
            this.open('split');
        },
        setSplitMode(mode) {
            this.split.mode = mode;
            this.computeSplit();
        },
        computeSplit() {
            const total = this.grandTotal;
            if (this.split.mode === 'equal' || this.split.mode === 'guest') {
                const ways = this.split.mode === 'guest' ? this.order.guests : this.split.ways;
                const each = Math.floor((total / ways) * 100) / 100;
                this.split.bills = Array.from({ length: ways }, (_, idx) => ({
                    idx,
                    label: this.split.mode === 'guest' ? `Guest ${idx + 1}` : `Bill ${idx + 1}`,
                    customer: idx === 0 ? this.customer.name : 'Guest',
                    total: idx === ways - 1 ? Math.round((total - each * (ways - 1)) * 100) / 100 : each,
                    status: idx === 0 ? 'paid' : 'pending', // seeded so the "partially settled" state is visible immediately
                    payments: idx === 0 ? [{ method: 'cash', label: 'Cash', amount: idx === ways - 1 ? Math.round((total - each * (ways - 1)) * 100) / 100 : each, reference: null, at: this.clock }] : [],
                }));
            } else if (this.split.mode === 'item') {
                this.split.assign = {};
                this.billableItems.forEach((i) => (this.split.assign[i.uid] = {}));
                this.split.bills = [
                    { idx: 0, label: 'Bill 1', customer: this.customer.name, total: 0, status: 'pending', payments: [] },
                    { idx: 1, label: 'Bill 2', customer: 'Guest', total: 0, status: 'pending', payments: [] },
                ];
            } else if (this.split.mode === 'amount') {
                this.split.amounts = [Math.round(total / 2), total - Math.round(total / 2)];
                this.split.bills = this.split.amounts.map((a, idx) => ({ idx, label: `Bill ${idx + 1}`, customer: idx === 0 ? this.customer.name : 'Guest', total: a, status: 'pending', payments: [] }));
            }
            this.split.active = true;
        },
        addSplitBill() {
            if (this.split.mode === 'amount') {
                this.split.amounts.push(0);
                this.split.bills.push({ idx: this.split.bills.length, label: `Bill ${this.split.bills.length + 1}`, customer: 'Guest', total: 0, status: 'pending', payments: [] });
            } else if (this.split.mode === 'item') {
                this.split.bills.push({ idx: this.split.bills.length, label: `Bill ${this.split.bills.length + 1}`, customer: 'Guest', total: 0, status: 'pending', payments: [] });
            }
        },
        setSplitAmount(idx, value) {
            this.split.amounts[idx] = Number(value) || 0;
            this.split.bills[idx].total = this.split.amounts[idx];
        },
        get splitAmountRemaining() {
            return this.grandTotal - this.split.amounts.reduce((s, v) => s + (Number(v) || 0), 0);
        },
        setSplitWays(n) {
            this.split.ways = n;
            this.computeSplit();
        },
        assignedQty(uid, billIdx) {
            return this.split.assign[uid]?.[billIdx] || 0;
        },
        unassignedQty(item) {
            const map = this.split.assign[item.uid] || {};
            const used = Object.values(map).reduce((s, v) => s + v, 0);
            return item.qty - used;
        },
        assignUnit(item, billIdx) {
            if (this.unassignedQty(item) <= 0) return;
            (this.split.assign[item.uid] ||= {});
            this.split.assign[item.uid][billIdx] = (this.split.assign[item.uid][billIdx] || 0) + 1;
            this.recomputeItemSplitTotals();
        },
        unassignUnit(item, billIdx) {
            if (!this.split.assign[item.uid]?.[billIdx]) return;
            this.split.assign[item.uid][billIdx] -= 1;
            this.recomputeItemSplitTotals();
        },
        recomputeItemSplitTotals() {
            this.split.bills.forEach((b) => {
                b.total = this.billableItems.reduce((s, i) => {
                    const unitNet = this.netAmount(i) / i.qty;
                    return s + unitNet * (this.split.assign[i.uid]?.[b.idx] || 0);
                }, 0);
            });
        },
        get splitUnassignedCount() {
            return this.billableItems.reduce((s, i) => s + this.unassignedQty(i), 0);
        },

        openSplitPay(bill) {
            this.splitPay = { idx: bill.idx, method: 'cash', amount: String(bill.total - bill.payments.reduce((s, p) => s + p.amount, 0)), reference: '' };
        },
        cancelSplitPay() {
            this.splitPay = { idx: null, method: 'cash', amount: '', reference: '' };
        },
        confirmSplitPay() {
            const bill = this.split.bills.find((b) => b.idx === this.splitPay.idx);
            if (!bill) return;
            const amount = Number(this.splitPay.amount) || 0;
            const due = bill.total - bill.payments.reduce((s, p) => s + p.amount, 0);
            if (amount <= 0 || amount > due + 0.01) return;
            const label = this.paymentMethods.find((m) => m.key === this.splitPay.method)?.label;
            bill.payments.push({ method: this.splitPay.method, label, amount, reference: this.splitPay.reference || null, at: this.clock });
            if (bill.payments.reduce((s, p) => s + p.amount, 0) >= bill.total - 0.01) bill.status = 'paid';
            this.cancelSplitPay();
            this.notify(`${bill.label} settled`, 'success');
        },
        get nextPendingSplitBill() {
            return this.split.bills.find((b) => b.status !== 'paid');
        },
        get allSplitBillsPaid() {
            return this.split.bills.length > 0 && this.split.bills.every((b) => b.status === 'paid');
        },
        clearSplit() {
            this.split = { active: false, mode: 'equal', ways: 3, assign: {}, amounts: [], bills: [] };
            this.notify('Split cleared — billing this as one invoice again');
        },

        /* ---------------------------------------------------------------
           Refund
           --------------------------------------------------------------- */
        openRefund() {
            this.refundDraft = { mode: 'full', method: this.payments[0]?.method || 'cash', amount: String(this.paidTotal), reason: '', items: [] };
            this.open('refund');
        },
        toggleRefundItem(uid) {
            const i = this.refundDraft.items.indexOf(uid);
            i === -1 ? this.refundDraft.items.push(uid) : this.refundDraft.items.splice(i, 1);
        },
        get refundItemsAmount() {
            return this.items.filter((i) => this.refundDraft.items.includes(i.uid)).reduce((s, i) => s + this.netAmount(i), 0);
        },
        get refundPreviewAmount() {
            if (this.refundDraft.mode === 'full') return this.paidTotal;
            if (this.refundDraft.mode === 'item') return this.refundItemsAmount;
            return Number(this.refundDraft.amount) || 0;
        },
        confirmRefund() {
            if (!this.refundDraft.reason || this.refundPreviewAmount <= 0) return;
            const commit = (approvedBy) => {
                const amount = this.refundPreviewAmount;
                this.refunds.push({ amount, method: this.refundDraft.method, reason: this.refundDraft.reason, mode: this.refundDraft.mode, approvedBy, at: this.clock });
                if (this.refundDraft.mode === 'item') {
                    this.items.filter((i) => this.refundDraft.items.includes(i.uid)).forEach((i) => { i.status = 'refunded'; i.refundReason = this.refundDraft.reason; i.refundAmount = this.netAmount(i); });
                }
                this.closeAll();
                this.notify(`Refund of ${this.money(amount)} recorded`, 'warn');
            };
            this.requestApproval({
                title: 'Manager approval required',
                detail: `Refunding ${this.money(this.refundPreviewAmount)} against ${this.invoice.code} needs manager authorization.`,
                onApprove: () => commit('Manager PIN'),
            });
        },

        /* ---------------------------------------------------------------
           Void bill
           --------------------------------------------------------------- */
        openVoid() {
            this.voidDraft = { reason: '' };
            this.open('void');
        },
        confirmVoid() {
            if (!this.voidDraft.reason) return;
            this.requestApproval({
                title: 'Manager approval required',
                detail: `Voiding ${this.invoice.code} (${this.money(this.grandTotal)}) needs manager authorization. This creates a reversal record — the original invoice is never deleted.`,
                onApprove: () => {
                    this.invoice.voided = true;
                    this.invoice.voidReason = this.voidDraft.reason;
                    this.closeAll();
                    this.notify(`${this.invoice.code} voided`, 'warn');
                },
            });
        },

        /* ---------------------------------------------------------------
           Table closure + navigation
           --------------------------------------------------------------- */
        openCloseTable() {
            this.open('closeTable');
        },
        confirmCloseTable() {
            // backend: free/clean the table and close the order
            this.notify(`${this.order.table} closed — table sent for cleaning`, 'success');
            window.location.href = this.tablesUrl;
        },
        goToPos() {
            window.location.href = this.posUrl;
        },
        startNewOrder() {
            window.location.href = this.tablesUrl;
        },

        /* ---------------------------------------------------------------
           Print (design-only stubs)
           --------------------------------------------------------------- */
        printRunningBill() {
            this.moreOpen = false;
            this.notify('Running bill sent to printer — not a final invoice');
        },
        printBill() {
            this.moreOpen = false;
            this.notify('Bill sent to printer');
        },
        printReceipt() {
            this.notify('Receipt printed');
        },
        emailReceipt() {
            this.notify(`Receipt emailed to ${this.customer.email || 'guest'}`);
        },
        whatsappReceipt() {
            this.notify('WhatsApp receipt — integration pending');
        },
        reprintInvoice() {
            this.moreOpen = false;
            this.notify(`${this.invoice.code} reprinted`);
        },

        /* ---------------------------------------------------------------
           More menu
           --------------------------------------------------------------- */
        moreActions: [
            { key: 'discount', label: 'Apply Discount' },
            { key: 'comp', label: 'Complimentary Item' },
            { key: 'customer', label: 'Add / Change Customer' },
            { key: 'gst', label: 'GST Invoice Details' },
            { key: 'runningBill', label: 'Print Running Bill' },
            { key: 'preview', label: 'Invoice Preview' },
            { key: 'history', label: 'Payment History' },
            { key: 'reprint', label: 'Reprint Invoice' },
            { key: 'refund', label: 'Refund' },
            { key: 'void', label: 'Void Bill', danger: true },
            { key: 'audit', label: 'Audit Information' },
        ],
        runMore(key) {
            this.moreOpen = false;
            switch (key) {
                case 'discount': return this.openDiscount();
                case 'comp': return this.notify('Select an item on the bill, then use its ⋮ menu to mark complimentary');
                case 'customer': return this.open('customer');
                case 'gst': return this.open('gst');
                case 'runningBill': return this.printRunningBill();
                case 'preview': return this.open('preview');
                case 'history': return this.open('history');
                case 'reprint': return this.reprintInvoice();
                case 'refund': return this.openRefund();
                case 'void': return this.openVoid();
                case 'audit': return this.open('audit');
            }
        },

        /* ---------------------------------------------------------------
           Keyboard
           --------------------------------------------------------------- */
        hotkey(e) {
            const map = {
                F2: () => this.open('customer'),
                F4: () => this.openDiscount(),
                F6: () => this.openSplit(),
                F8: () => this.$refs.payAmount?.focus(),
                F9: () => this.dueAmount <= 0 && this.completePayment(),
            };
            if (e.ctrlKey && e.key.toLowerCase() === 'p') {
                e.preventDefault();
                this.printBill();
                return;
            }
            if (map[e.key]) {
                e.preventDefault();
                map[e.key]();
                return;
            }
            if (e.key === 'Escape') {
                if (this.moreOpen) return (this.moreOpen = false);
                if (this.stack.length) return this.back();
            }
        },
    };
}
