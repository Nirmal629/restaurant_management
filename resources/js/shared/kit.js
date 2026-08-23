/**
 * Shared Alpine helpers for the admin modules (Reservations, Customers, Menu,
 * Inventory, Purchases, Expenses, Reports, Employees, Settings).
 *
 * Every admin store spreads `overlayMixin()` into itself so the modal/drawer
 * stack behaves identically to POS/Floor/KDS/Billing without re-deriving the
 * same handful of methods nine times.
 */

export function overlayMixin() {
    return {
        stack: [],
        toast: null,

        get overlay() {
            return this.stack.length ? this.stack[this.stack.length - 1] : null;
        },
        open(name) {
            if (this.overlay === name) return;
            this.stack.push(name);
            this.$nextTick(() => this.focusFirst());
        },
        openOnly(name) {
            this.stack = [name];
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
    };
}

/** Every admin table uses the same page/perPage/total shape. */
export function paginationMixin(perPage = 10) {
    return {
        page: 1,
        perPage,
        setPage(p, total) {
            this.page = Math.min(Math.max(1, p), Math.max(1, Math.ceil(total / this.perPage)));
        },
        pageSlice(list) {
            const start = (this.page - 1) * this.perPage;
            return list.slice(start, start + this.perPage);
        },
        pageCount(total) {
            return Math.max(1, Math.ceil(total / this.perPage));
        },
    };
}

export const money = (n, decimals = 0) =>
    '₹' + Number(n || 0).toLocaleString('en-IN', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });

/** DD/MM/YYYY, per the brief — accepts a Date or a 'YYYY-MM-DD' string. */
export function formatDate(d) {
    const date = d instanceof Date ? d : new Date(d);
    if (Number.isNaN(date.getTime())) return String(d);
    const dd = String(date.getDate()).padStart(2, '0');
    const mm = String(date.getMonth() + 1).padStart(2, '0');
    return `${dd}/${mm}/${date.getFullYear()}`;
}

export function initials(name) {
    return (name || '')
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((w) => w[0].toUpperCase())
        .join('');
}
