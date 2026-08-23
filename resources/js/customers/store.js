import { overlayMixin, paginationMixin, money, formatDate, initials } from '../shared/kit.js';
import { CUSTOMERS, DISCOUNT_LOG, OPERATOR, TAGS, VENUE } from './demo-data.js';

export default function customersApp() {
    const boot = window.customerModule || {};
    const routes = window.customerRoutes || {};
    const normalize = (customer) => ({
        ...customer,
        tags: [...(customer.tags || [])],
        favoriteItems: [...(customer.favoriteItems || [])],
        recentOrders: [...(customer.recentOrders || [])],
    });

    return {
        ...overlayMixin(),
        ...paginationMixin(10),
        venue: boot.venue || VENUE,
        operator: OPERATOR,
        tags: boot.tags || TAGS,
        money,
        formatDate,
        initials,

        customers: (boot.customers || CUSTOMERS).map(normalize),
        coupons: [...(boot.coupons || [])],
        discountLog: DISCOUNT_LOG,
        loading: false,
        query: '',
        segment: 'all',
        openRowMenu: null,
        activeId: null,
        activeTab: 'overview',
        showProfile: false,
        showForm: false,
        showLoyalty: false,
        showNote: false,
        showCoupons: false,
        showCouponForm: false,
        saving: false,
        profileOpenArmed: false,
        draft: {},
        loyaltyDraft: { points: '', reason: '' },
        noteDraft: '',
        couponDraft: {},

        init() {
            this.loading = true;
            this.sortCustomers();
            setTimeout(() => (this.loading = false), 500);
        },
        open(name) {
            this.stack = this.stack.filter((item) => item !== name);
            this.stack.push(name);
            this.syncOverlayFlags();
            this.$nextTick(() => this.focusFirst());
        },
        swap(name) {
            if (this.stack.length) this.stack.pop();
            this.open(name);
        },
        back() {
            const current = this.overlay;
            this.stack = current ? this.stack.filter((item) => item !== current) : [];
            this.cleanupOverlayState();
        },
        closeAll() {
            this.stack = [];
            this.cleanupOverlayState();
        },
        syncOverlayFlags() {
            this.showProfile = this.stack.includes('profile');
            this.showForm = this.stack.includes('form');
            this.showLoyalty = this.stack.includes('loyalty');
            this.showNote = this.stack.includes('note');
            this.showCoupons = this.stack.includes('coupons');
            this.showCouponForm = this.stack.includes('couponForm');
        },
        cleanupOverlayState() {
            this.syncOverlayFlags();
            if (!this.showProfile && !this.showLoyalty && !this.showNote && !this.showForm && !this.showCoupons && !this.showCouponForm) {
                this.activeId = null;
                this.activeTab = 'overview';
            }
            if (!this.showForm) this.draft = {};
            if (!this.showLoyalty) this.loyaltyDraft = { points: '', reason: '' };
            if (!this.showNote) this.noteDraft = '';
            if (!this.showCouponForm) this.couponDraft = {};
            this.openRowMenu = null;
            this.profileOpenArmed = false;
        },

        customer(id) {
            return this.customers.find((c) => c.id === id);
        },
        get activeCustomer() {
            return this.customer(this.activeId);
        },
        thisMonth(dateStr) {
            if (!dateStr) return false;
            const now = new Date();
            const d = new Date(dateStr);
            return d.getMonth() === now.getMonth();
        },
        segmentOf(c) {
            if (c.vip) return 'vip';
            if (c.visits <= 1) return 'new';
            const days = (Date.now() - new Date(c.lastVisit)) / 86400000;
            if (days > 60) return 'inactive';
            return 'returning';
        },

        get filtered() {
            let list = [...this.customers];
            if (this.segment === 'birthday') list = list.filter((c) => this.thisMonth(c.birthday));
            else if (this.segment === 'anniversary') list = list.filter((c) => this.thisMonth(c.anniversary));
            else if (this.segment !== 'all') list = list.filter((c) => this.segmentOf(c) === this.segment);
            if (this.query.trim()) {
                const q = this.query.trim().toLowerCase();
                list = list.filter((c) => [c.name, c.phone, c.email].join(' ').toLowerCase().includes(q));
            }
            return list.sort((a, b) => Number(b.id) - Number(a.id));
        },
        get paged() {
            return this.pageSlice(this.filtered);
        },
        clearFilters() {
            this.query = '';
            this.segment = 'all';
            this.page = 1;
        },

        get summary() {
            return {
                total: this.customers.length,
                vip: this.customers.filter((c) => c.vip).length,
                newThis: this.customers.filter((c) => this.segmentOf(c) === 'new').length,
                inactive: this.customers.filter((c) => this.segmentOf(c) === 'inactive').length,
            };
        },

        armProfileOpen() {
            if (!this.overlay) this.profileOpenArmed = true;
        },
        openProfile(c, force = false) {
            if (!force && !this.profileOpenArmed) return;
            if (this.overlay && this.overlay !== 'profile') return;
            this.profileOpenArmed = false;
            this.openRowMenu = null;
            this.activeId = c.id;
            this.activeTab = 'overview';
            this.stack = ['profile'];
            this.syncOverlayFlags();
        },
        openCreate() {
            this.openRowMenu = null;
            this.activeId = null;
            this.activeTab = 'overview';
            this.profileOpenArmed = false;
            this.draft = { id: null, name: '', phone: '', email: '', birthday: '', anniversary: '', address: '', gstin: '', tags: [] };
            this.stack = ['form'];
            this.syncOverlayFlags();
            this.$nextTick(() => this.focusFirst());
        },
        openEdit(c) {
            this.openRowMenu = null;
            this.profileOpenArmed = false;
            this.draft = { id: c.id, name: c.name, phone: c.phone, email: c.email, birthday: c.birthday, anniversary: c.anniversary, address: c.address, gstin: c.gstin, tags: [...(c.tags || [])] };
            this.stack = ['form'];
            this.syncOverlayFlags();
            this.$nextTick(() => this.focusFirst());
        },
        toggleDraftTag(t) {
            const i = this.draft.tags.indexOf(t);
            i === -1 ? this.draft.tags.push(t) : this.draft.tags.splice(i, 1);
        },
        async saveCustomer() {
            const d = this.draft;
            if (!d.name.trim() || d.phone.trim().length < 10) return;

            const result = await this.request(d.id ? `${routes.update}/${d.id}` : routes.store, {
                method: d.id ? 'PUT' : 'POST',
                body: JSON.stringify({
                    name: d.name,
                    phone: d.phone,
                    email: d.email || null,
                    birthday: d.birthday || null,
                    anniversary: d.anniversary || null,
                    address: d.address || null,
                    gstin: d.gstin || null,
                    tags: d.tags || [],
                }),
            });
            if (!result) return;

            if (d.id) this.replaceCustomer(result.customer);
            else this.customers.unshift(normalize(result.customer));

            this.sortCustomers();
            this.clearFilters();
            this.notify(result.message || `${d.name} saved`, 'success');
            this.closeAll();
        },
        async toggleVip(c) {
            const result = await this.request(`${routes.update}/${c.id}/vip`, { method: 'PATCH' });
            if (!result) return;
            this.replaceCustomer(result.customer);
            this.notify(result.message || 'VIP updated', 'success');
        },

        openLoyalty(c) {
            this.activeId = c.id;
            this.loyaltyDraft = { points: '', reason: '' };
            this.stack = ['profile', 'loyalty'];
            this.syncOverlayFlags();
            this.$nextTick(() => this.focusFirst());
        },
        async applyLoyaltyAdjust(c) {
            const p = Number(this.loyaltyDraft.points);
            if (!p || !this.loyaltyDraft.reason) return;

            const result = await this.request(`${routes.update}/${c.id}/loyalty`, {
                method: 'PATCH',
                body: JSON.stringify({ points: p, reason: this.loyaltyDraft.reason }),
            });
            if (!result) return;

            this.replaceCustomer(result.customer);
            (this.discountLog[c.id] ||= []).unshift(result.log);
            this.stack = ['profile'];
            this.cleanupOverlayState();
            this.notify(result.message || `Loyalty points updated for ${c.name}`, 'success');
        },

        openNote(c) {
            this.activeId = c.id;
            this.noteDraft = c.notes || '';
            this.stack = ['profile', 'note'];
            this.syncOverlayFlags();
            this.$nextTick(() => this.focusFirst());
        },
        async saveNote(c) {
            const result = await this.request(`${routes.update}/${c.id}/note`, {
                method: 'PATCH',
                body: JSON.stringify({ notes: this.noteDraft || null }),
            });
            if (!result) return;

            this.replaceCustomer(result.customer);
            this.stack = ['profile'];
            this.cleanupOverlayState();
            this.notify(result.message || 'Note saved', 'success');
        },

        openCoupons() {
            this.stack = ['coupons'];
            this.syncOverlayFlags();
        },
        openCouponCreate() {
            this.couponDraft = {
                id: null, code: '', name: '', type: 'percent', value: '', minBillAmount: 0,
                maxDiscountAmount: '', startsAt: '', expiresAt: '', usageLimit: '',
                perCustomerLimit: 1, walkinAllowed: true, active: true,
            };
            this.stack = ['couponForm'];
            this.syncOverlayFlags();
            this.$nextTick(() => this.focusFirst());
        },
        openCouponEdit(coupon) {
            this.couponDraft = { ...coupon };
            this.stack = ['couponForm'];
            this.syncOverlayFlags();
            this.$nextTick(() => this.focusFirst());
        },
        async saveCoupon() {
            const d = this.couponDraft;
            if (!d.code?.trim() || !d.name?.trim() || !(Number(d.value) > 0)) return;

            const result = await this.request(d.id ? `${routes.coupons}/${d.id}` : routes.coupons, {
                method: d.id ? 'PUT' : 'POST',
                body: JSON.stringify({
                    code: d.code,
                    name: d.name,
                    type: d.type,
                    value: Number(d.value),
                    minBillAmount: Number(d.minBillAmount) || 0,
                    maxDiscountAmount: d.maxDiscountAmount === '' || d.maxDiscountAmount === null ? null : Number(d.maxDiscountAmount),
                    startsAt: d.startsAt || null,
                    expiresAt: d.expiresAt || null,
                    usageLimit: d.usageLimit === '' || d.usageLimit === null ? null : Number(d.usageLimit),
                    perCustomerLimit: d.perCustomerLimit === '' || d.perCustomerLimit === null ? null : Number(d.perCustomerLimit),
                    walkinAllowed: !!d.walkinAllowed,
                    active: !!d.active,
                }),
            });
            if (!result) return;

            const index = this.coupons.findIndex((c) => c.id === result.coupon.id);
            if (index >= 0) this.coupons.splice(index, 1, result.coupon);
            else this.coupons.unshift(result.coupon);
            this.notify(result.message || 'Coupon saved', 'success');
            this.stack = ['coupons'];
            this.cleanupOverlayState();
        },
        async deleteCoupon(coupon) {
            if (!confirm(`Delete coupon ${coupon.code}?`)) return;
            const result = await this.request(`${routes.coupons}/${coupon.id}`, { method: 'DELETE' });
            if (!result) return;
            this.coupons = this.coupons.filter((c) => c.id !== coupon.id);
            this.notify(result.message || 'Coupon deleted', 'success');
        },

        replaceCustomer(customer) {
            const next = normalize(customer);
            const index = this.customers.findIndex((c) => c.id === next.id);
            if (index >= 0) this.customers.splice(index, 1, next);
            else this.customers.unshift(next);
        },
        sortCustomers() {
            this.customers.sort((a, b) => Number(b.id) - Number(a.id));
        },
        async request(url, options = {}) {
            if (!url || this.saving) return null;
            this.saving = true;

            try {
                const response = await fetch(url, {
                    ...options,
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        ...(options.headers || {}),
                    },
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    const firstError = data.errors ? Object.values(data.errors).flat()[0] : null;
                    this.notify(firstError || data.message || 'Customer update failed', 'warn');
                    return null;
                }

                return data;
            } catch (error) {
                this.notify('Network error while saving customer', 'warn');
                return null;
            } finally {
                this.saving = false;
            }
        },
    };
}
