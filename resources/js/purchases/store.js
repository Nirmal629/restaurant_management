import { overlayMixin, paginationMixin, money } from '../shared/kit.js';

const boot = window.purchaseModule || {};
const routeConfig = window.purchaseRoutes || {};

export default function purchasesApp() {
    return {
        ...overlayMixin(),
        ...paginationMixin(8),
        venue: boot.venue || { name: 'Restaurant', branch: 'Main Branch' },
        operator: boot.operator || { name: 'System' },
        approvalReasons: boot.approvalReasons || [],
        routes: routeConfig,
        money,

        tab: 'po',
        orders: (boot.orders || []).map((o) => ({ ...o, items: (o.items || []).map((i) => ({ ...i })) })),
        receipts: (boot.receipts || []).map((g) => ({ ...g, items: (g.items || []).map((i) => ({ ...i })) })),
        suppliers: (boot.suppliers || []).map((s) => ({ ...s, items: [...(s.items || [])] })),

        query: '',
        statusFilter: 'all',
        openRowMenu: null,
        activeId: null,
        purchaseModal: null,
        approvalDraft: {},
        poDraft: {},
        poFormMode: 'create',
        grnDraft: {},
        saving: false,

        init() {
            const params = new URLSearchParams(window.location.search);
            if (params.has('ingredient')) {
                this.openCreatePO();
                Object.assign(this.poDraft.items[0], {
                    ingredient: params.get('ingredient') || '',
                    qty: params.get('qty') || '',
                    unit: params.get('unit') || 'KG',
                });
                this.poDraft.supplier = params.get('supplier') || this.poDraft.supplier;
            }
        },

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
        setTab(tab) {
            if (this.tab === tab) return;
            this.tab = tab;
            this.closeAll();
            this.openRowMenu = null;
            this.activeId = null;
            this.page = 1;
        },
        openPurchaseModal(name) {
            this.purchaseModal = name;
            this.stack = [name];
            this.$nextTick(() => this.focusFirst());
        },
        closeAll() {
            this.purchaseModal = null;
            this.stack = [];
            this.openRowMenu = null;
        },
        canOpen(names) {
            const allowed = {
                po: ['poDetail', 'poForm', 'approve'],
                grn: ['grnForm', 'grnDetail'],
                suppliers: ['supplierDetail'],
            }[this.tab] || [];
            const list = Array.isArray(names) ? names : [names];
            return list.every((name) => allowed.includes(name));
        },

        lineAmount(l) {
            return Number(l.qty || 0) * Number(l.rate || 0) * (1 + Number(l.tax || 0) / 100);
        },
        poSubtotal(o) {
            return (o.items || []).reduce((s, l) => s + Number(l.qty || 0) * Number(l.rate || 0), 0);
        },
        poTax(o) {
            return (o.items || []).reduce((s, l) => s + Number(l.qty || 0) * Number(l.rate || 0) * (Number(l.tax || 0) / 100), 0);
        },
        poTotal(o) {
            return Math.round(this.poSubtotal(o) + this.poTax(o) - Number(o.discount || 0) + Number(o.otherCharges || 0));
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
                outstanding: this.suppliers.reduce((s, sup) => s + Number(sup.outstanding || 0), 0),
            };
        },

        openDetail(o) {
            if (!this.canOpen('poDetail')) return;
            this.openRowMenu = null;
            this.activeId = o.id;
            this.openPurchaseModal('poDetail');
        },
        openCreatePO() {
            if (!this.canOpen('poForm')) return;
            this.openRowMenu = null;
            this.activeId = null;
            this.poFormMode = 'create';
            this.poDraft = { supplier: this.suppliers[0]?.name || '', expectedDelivery: '', reference: '', notes: '', items: [{ ingredient: '', currentStock: 0, qty: '', unit: 'KG', rate: '', tax: 0 }], discount: 0, otherCharges: 0 };
            this.openPurchaseModal('poForm');
        },
        openEditPO(o) {
            if (!this.canOpen('poForm') || ['received', 'cancelled'].includes(o.status)) return;
            this.openRowMenu = null;
            this.activeId = o.id;
            this.poFormMode = 'edit';
            this.poDraft = { ...o, items: (o.items || []).map((l) => ({ ...l })) };
            this.openPurchaseModal('poForm');
        },
        addPoLine() {
            this.poDraft.items.push({ ingredient: '', currentStock: 0, qty: '', unit: 'KG', rate: '', tax: 0 });
        },
        removePoLine(i) {
            if (this.poDraft.items.length > 1) this.poDraft.items.splice(i, 1);
        },
        get poDraftSubtotal() {
            return this.poDraft.items.reduce((s, l) => s + (Number(l.qty) || 0) * (Number(l.rate) || 0), 0);
        },
        get poDraftTax() {
            return this.poDraft.items.reduce((s, l) => s + (Number(l.qty) || 0) * (Number(l.rate) || 0) * ((Number(l.tax) || 0) / 100), 0);
        },
        get poDraftTotal() {
            return Math.round(this.poDraftSubtotal + this.poDraftTax - (Number(this.poDraft.discount) || 0) + (Number(this.poDraft.otherCharges) || 0));
        },
        async savePO() {
            const d = this.poDraft;
            if (!d.supplier || !d.items.length || d.items.some((l) => !l.ingredient || !Number(l.qty))) return;
            this.saving = true;
            try {
                const response = await this.request(d.dbId ? `${this.routes.orders}/${d.dbId}` : this.routes.orders, d.dbId ? 'PUT' : 'POST', d);
                this.upsertOrder(response.order);
                this.closeAll();
                this.notify(response.message || 'Purchase order saved', 'success');
            } catch (error) {
                this.notify(error.message || 'Could not save purchase order', 'error');
            } finally {
                this.saving = false;
            }
        },

        requestApproval(o) {
            return this.changeOrderStatus(o, 'approval_pending');
        },
        openApprove(o) {
            if (!this.canOpen('approve')) return;
            this.openRowMenu = null;
            this.activeId = o.id;
            this.approvalDraft = { reason: '' };
            this.openPurchaseModal('approve');
        },
        confirmApprove() {
            const o = this.activeOrder;
            if (!o) return;
            return this.changeOrderStatus(o, 'approved');
        },
        markOrdered(o) {
            return this.changeOrderStatus(o, 'ordered');
        },
        cancelPO(o) {
            return this.changeOrderStatus(o, 'cancelled');
        },
        async changeOrderStatus(o, status) {
            this.openRowMenu = null;
            this.saving = true;
            try {
                const response = await this.request(`${this.routes.orders}/${o.dbId}/status`, 'PATCH', { status });
                this.upsertOrder(response.order);
                this.closeAll();
                this.notify(response.message || 'Purchase order updated', status === 'cancelled' ? 'warn' : 'success');
            } catch (error) {
                this.notify(error.message || 'Could not update purchase order', 'error');
            } finally {
                this.saving = false;
            }
        },
        async deletePO(o) {
            this.openRowMenu = null;
            if (!window.confirm(`Delete ${o.id}?`)) return;
            try {
                const response = await this.request(`${this.routes.orders}/${o.dbId}`, 'DELETE');
                this.orders = response.orders || this.orders.filter((x) => x.id !== o.id);
                this.closeAll();
                this.notify(response.message || 'Purchase order deleted', 'success');
            } catch (error) {
                this.notify(error.message || 'Could not delete purchase order', 'error');
            }
        },

        openReceiveGoods(o) {
            if (!this.canOpen('grnForm')) return;
            this.openRowMenu = null;
            this.activeId = o.id;
            this.grnDraft = { poRef: o.id, supplier: o.supplier, invoiceNumber: '', receivedDate: new Date().toISOString().slice(0, 10), items: (o.items || []).map((l) => ({ ingredient: l.ingredient, ordered: l.qty, prevReceived: l.prevReceived || 0, receivedNow: Math.max(0, Number(l.qty || 0) - Number(l.prevReceived || 0)), rejected: 0 })) };
            this.openPurchaseModal('grnForm');
        },
        get receivableOrders() {
            return this.orders.filter((o) => ['ordered', 'partially_received'].includes(o.status));
        },
        openCreateGRN() {
            if (!this.canOpen('grnForm')) return;
            const order = this.receivableOrders[0];
            if (!order) {
                this.notify('No ordered purchase order is ready to receive', 'warn');
                return;
            }
            this.openReceiveGoods(order);
        },
        selectGrnOrder() {
            const order = this.orders.find((o) => o.id === this.grnDraft.poRef);
            if (!order) return;
            this.activeId = order.id;
            this.grnDraft = { ...this.grnDraft, poRef: order.id, supplier: order.supplier, items: (order.items || []).map((l) => ({ ingredient: l.ingredient, ordered: l.qty, prevReceived: l.prevReceived || 0, receivedNow: Math.max(0, Number(l.qty || 0) - Number(l.prevReceived || 0)), rejected: 0 })) };
        },
        acceptedQty(l) {
            return Math.max(0, Number(l.receivedNow || 0) - Number(l.rejected || 0));
        },
        async saveGRN() {
            const d = this.grnDraft;
            if (!d.invoiceNumber?.trim()) return;
            this.saving = true;
            try {
                const response = await this.request(this.routes.receipts, 'POST', d);
                this.receipts.unshift(response.receipt);
                this.orders = response.orders || this.orders;
                this.closeAll();
                this.notify(response.message || 'Goods receipt recorded', 'success');
            } catch (error) {
                this.notify(error.message || 'Could not save goods receipt', 'error');
            } finally {
                this.saving = false;
            }
        },
        openGrnDetail(g) {
            if (!this.canOpen('grnDetail')) return;
            this.openRowMenu = null;
            this.activeId = g.id;
            this.openPurchaseModal('grnDetail');
        },
        get activeGrn() {
            return this.receipts.find((g) => g.id === this.activeId);
        },
        async deleteGRN(g) {
            if (!window.confirm(`Delete ${g.id}?`)) return;
            try {
                const response = await this.request(`${this.routes.receipts}/${g.dbId}`, 'DELETE');
                this.receipts = response.receipts || this.receipts.filter((x) => x.id !== g.id);
                this.orders = response.orders || this.orders;
                this.closeAll();
                this.notify(response.message || 'Goods receipt deleted', 'success');
            } catch (error) {
                this.notify(error.message || 'Could not delete goods receipt', 'error');
            }
        },

        supplier(id) {
            return this.suppliers.find((s) => s.id === id);
        },
        get activeSupplier() {
            return this.supplier(this.activeId);
        },
        openSupplierDetail(s) {
            if (!this.canOpen('supplierDetail')) return;
            this.openRowMenu = null;
            this.activeId = s.id;
            this.openPurchaseModal('supplierDetail');
        },
        supplierHistory(name) {
            return this.orders.filter((o) => o.supplier === name);
        },
        upsertOrder(order) {
            const index = this.orders.findIndex((o) => o.id === order.id);
            if (index === -1) this.orders.unshift(order);
            else this.orders.splice(index, 1, order);
        },
        printPurchases() {
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
