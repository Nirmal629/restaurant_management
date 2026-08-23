const boot = window.ordersModule || {};
const routes = window.ordersRoutes || {};
import { subscribeRealtime } from '../shared/realtime.js';

const inr = (n) => '₹' + Number(n || 0).toLocaleString('en-IN', { maximumFractionDigits: 0 });
const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

export default function ordersApp() {
    return {
        orders: (boot.orders || []).map((o) => ({ ...o })),
        summary: boot.summary || { active: 0, kitchen: 0, billing: 0, paidToday: 0 },
        activeOrderId: boot.activeOrderId || null,
        filter: 'active',
        query: '',
        toast: null,
        saving: false,

        money: inr,
        init() {
            this._unsubscribeRealtime = subscribeRealtime(['orders', 'kitchen', 'billing', 'tables', 'pos'], () => this.refreshOrders());
        },
        get activeOrder() {
            return this.orders.find((o) => o.id === this.activeOrderId) || this.filteredOrders[0] || null;
        },
        get filteredOrders() {
            const q = this.query.trim().toLowerCase();
            return this.orders.filter((order) => {
                const bucket = this.filter === 'all'
                    || (this.filter === 'active' && ['open', 'billing'].includes(order.status))
                    || (this.filter === 'kitchen' && order.kitchenOpen > 0)
                    || (this.filter === 'billing' && ['billing', 'paid'].includes(order.status))
                    || (this.filter === 'history' && ['completed', 'cancelled'].includes(order.status));
                const text = `${order.code} ${order.table} ${order.customer} ${order.waiter} ${order.invoiceCode || ''}`.toLowerCase();
                return bucket && (!q || text.includes(q));
            });
        },
        notify(message, tone = 'info') {
            this.toast = { message, tone };
            clearTimeout(this._timer);
            this._timer = setTimeout(() => (this.toast = null), 2400);
        },
        select(order) {
            this.activeOrderId = order.id;
        },
        applyPayload(data) {
            if (data.orders) this.orders = data.orders.map((o) => ({ ...o }));
            if (data.summary) this.summary = data.summary;
            if (data.order) this.activeOrderId = data.order.id;
        },
        async refreshOrders() {
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
            if (this.saving) return;
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
                    this.notify(first || data.message || 'Order update failed', 'warn');
                    return;
                }
                this.applyPayload(data);
                this.notify(data.message || 'Order updated', 'success');
            } catch (error) {
                this.notify('Network error while updating order', 'warn');
            } finally {
                this.saving = false;
            }
        },
        setOrderStatus(order, status) {
            return this.request(`${routes.orders}/${order.id}/status`, { status });
        },
        setItemStatus(item, status) {
            return this.request(`${routes.items}/${item.id}/status`, { status });
        },
        orderStatusLabel(status) {
            return { open: 'Open', billing: 'Billing', paid: 'Paid', completed: 'Completed', cancelled: 'Cancelled' }[status] || status;
        },
        itemStatusLabel(status) {
            return { unsent: 'New', sent: 'Sent', accepted: 'Accepted', preparing: 'Preparing', ready: 'Ready', served: 'Served', cancelled: 'Cancelled', unavailable: 'Unavailable' }[status] || status;
        },
        statusClass(status) {
            return {
                open: 'border-sky-300 bg-sky-50 text-sky-800',
                billing: 'border-amber-400 bg-amber-50 text-amber-800',
                paid: 'border-emerald-400 bg-emerald-50 text-emerald-800',
                completed: 'border-slate-300 bg-slate-100 text-slate-600',
                cancelled: 'border-rose-300 bg-rose-50 text-rose-700',
                ready: 'border-emerald-300 bg-emerald-50 text-emerald-800',
                served: 'border-slate-300 bg-slate-100 text-slate-600',
                preparing: 'border-orange-300 bg-orange-50 text-orange-800',
                accepted: 'border-sky-300 bg-sky-50 text-sky-800',
                sent: 'border-violet-300 bg-violet-50 text-violet-800',
            }[status] || 'border-slate-300 bg-slate-100 text-slate-600';
        },
        billingUrl(order) {
            return `${routes.billing}?order=${order.id}`;
        },
    };
}
