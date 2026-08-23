import { overlayMixin, paginationMixin, money } from '../shared/kit.js';
import { APPROVAL_REASONS, GOODS_RECEIPTS, OPERATOR, PURCHASE_ORDERS, SUPPLIERS, VENUE } from './demo-data.js';

export default function purchasesApp() {
    return {
        ...overlayMixin(),
        ...paginationMixin(8),
        venue: VENUE,
        operator: OPERATOR,
        approvalReasons: APPROVAL_REASONS,
        money,

        tab: 'po', // po | grn | suppliers
        orders: PURCHASE_ORDERS.map((o) => ({ ...o, items: o.items.map((i) => ({ ...i })) })),
        receipts: GOODS_RECEIPTS.map((g) => ({ ...g, items: g.items.map((i) => ({ ...i })) })),
        suppliers: SUPPLIERS.map((s) => ({ ...s, items: [...s.items] })),

        query: '',
        statusFilter: 'all',
        openRowMenu: null,
        activeId: null,
        approvalDraft: {},
        poDraft: {},
        grnDraft: {},

        statusLabel(s) {
            return { draft: 'Draft', approval_pending: 'Approval Pending', approved: 'Approved', ordered: 'Ordered', partially_received: 'Partially Received', received: 'Received', cancelled: 'Cancelled' }[s] || s;
        },
        statusClass(s) {
            return {
                draft: 'border-slate-300 bg-slate-100 text-slate-600',
                approval_pending: 'border-amber-400 bg-amber-50 text-amber-800',
                approved: 'border-sky-300 bg-sky-50 text-sky-800',
                ordered: 'border-violet-300 bg-violet-50 text-violet-800',
                partially_received: 'border-orange-300 bg-orange-50 text-orange-800',
                received: 'border-emerald-400 bg-emerald-50 text-emerald-800',
                cancelled: 'border-rose-300 bg-rose-50 text-rose-700',
            }[s] || 'border-slate-300 bg-slate-100 text-slate-600';
        },

        lineAmount(l) {
            return l.qty * l.rate * (1 + (l.tax || 0) / 100);
        },
        poSubtotal(o) {
            return o.items.reduce((s, l) => s + l.qty * l.rate, 0);
        },
        poTax(o) {
            return o.items.reduce((s, l) => s + l.qty * l.rate * ((l.tax || 0) / 100), 0);
        },
        poTotal(o) {
            return Math.round(this.poSubtotal(o) + this.poTax(o) - (o.discount || 0) + (o.otherCharges || 0));
        },

        order(id) {
            return this.orders.find((o) => o.id === id);
        },
        get activeOrder() {
            return this.order(this.activeId);
        },

        get filteredOrders() {
            let list = [...this.orders];
            if (this.statusFilter !== 'all') list = list.filter((o) => o.status === this.statusFilter);
            if (this.query.trim()) {
                const q = this.query.trim().toLowerCase();
                list = list.filter((o) => [o.id, o.supplier].join(' ').toLowerCase().includes(q));
            }
            return list;
        },
        get pagedOrders() {
            return this.pageSlice(this.filteredOrders);
        },
        clearFilters() {
            this.query = '';
            this.statusFilter = 'all';
            this.page = 1;
        },

        get summary() {
            return {
                open: this.orders.filter((o) => !['received', 'cancelled'].includes(o.status)).length,
                pendingApproval: this.orders.filter((o) => o.status === 'approval_pending').length,
                value: this.orders.filter((o) => !['cancelled'].includes(o.status)).reduce((s, o) => s + this.poTotal(o), 0),
                outstanding: this.suppliers.reduce((s, sup) => s + sup.outstanding, 0),
            };
        },

        openDetail(o) {
            this.openRowMenu = null;
            this.activeId = o.id;
            this.open('poDetail');
        },
        openCreatePO() {
            this.openRowMenu = null;
            this.poDraft = { supplier: this.suppliers[0]?.name, expectedDelivery: '', reference: '', notes: '', items: [{ ingredient: '', currentStock: 0, qty: '', unit: 'KG', rate: '', tax: 0 }], discount: 0, otherCharges: 0 };
            this.open('poForm');
        },
        addPoLine() {
            this.poDraft.items.push({ ingredient: '', currentStock: 0, qty: '', unit: 'KG', rate: '', tax: 0 });
        },
        removePoLine(i) {
            this.poDraft.items.splice(i, 1);
        },
        get poDraftSubtotal() {
            return this.poDraft.items.reduce((s, l) => s + (Number(l.qty) || 0) * (Number(l.rate) || 0), 0);
        },
        get poDraftTax() {
            return this.poDraft.items.reduce((s, l) => s + (Number(l.qty) || 0) * (Number(l.rate) || 0) * ((l.tax || 0) / 100), 0);
        },
        get poDraftTotal() {
            return Math.round(this.poDraftSubtotal + this.poDraftTax - (Number(this.poDraft.discount) || 0) + (Number(this.poDraft.otherCharges) || 0));
        },
        savePO() {
            const d = this.poDraft;
            if (!d.supplier || !d.items.length || d.items.some((l) => !l.ingredient || !l.qty)) return;
            this.orders.unshift({ ...d, id: 'PO-2026-' + (85 + this.orders.length), date: '23/08/2026', status: 'draft', createdBy: this.operator.name, approvedBy: null });
            this.closeAll();
            this.notify('Purchase order saved as draft', 'success');
        },

        requestApproval(o) {
            this.openRowMenu = null;
            o.status = 'approval_pending';
            this.notify(`${o.id} submitted for approval`);
        },
        openApprove(o) {
            this.openRowMenu = null;
            this.activeId = o.id;
            this.approvalDraft = { reason: '' };
            this.open('approve');
        },
        confirmApprove() {
            const o = this.activeOrder;
            if (!o) return;
            o.status = 'approved';
            o.approvedBy = 'Rakesh Singh';
            this.closeAll();
            this.notify(`${o.id} approved`, 'success');
        },
        markOrdered(o) {
            this.openRowMenu = null;
            o.status = 'ordered';
            this.notify(`${o.id} marked as ordered with supplier`);
        },
        cancelPO(o) {
            this.openRowMenu = null;
            o.status = 'cancelled';
            this.notify(`${o.id} cancelled`, 'warn');
        },

        /* GRN */
        openReceiveGoods(o) {
            this.openRowMenu = null;
            this.grnDraft = { poRef: o.id, supplier: o.supplier, invoiceNumber: '', receivedDate: '23/08/2026', items: o.items.map((l) => ({ ingredient: l.ingredient, ordered: l.qty, prevReceived: 0, receivedNow: l.qty, rejected: 0 })) };
            this.open('grnForm');
        },
        acceptedQty(l) {
            return Math.max(0, l.receivedNow - l.rejected);
        },
        saveGRN() {
            const d = this.grnDraft;
            if (!d.invoiceNumber.trim()) return;
            this.receipts.unshift({ ...d, id: 'GRN-2026-' + (43 + this.receipts.length) });
            const o = this.orders.find((x) => x.id === d.poRef);
            if (o) {
                const fullyReceived = d.items.every((l) => l.receivedNow + l.prevReceived >= l.ordered);
                o.status = fullyReceived ? 'received' : 'partially_received';
            }
            this.closeAll();
            this.notify('Goods receipt recorded — inventory updated', 'success');
        },
        openGrnDetail(g) {
            this.activeId = g.id;
            this.open('grnDetail');
        },
        get activeGrn() {
            return this.receipts.find((g) => g.id === this.activeId);
        },

        /* Suppliers */
        supplier(id) {
            return this.suppliers.find((s) => s.id === id);
        },
        get activeSupplier() {
            return this.supplier(this.activeId);
        },
        openSupplierDetail(s) {
            this.activeId = s.id;
            this.open('supplierDetail');
        },
        supplierHistory(name) {
            return this.orders.filter((o) => o.supplier === name);
        },
    };
}
