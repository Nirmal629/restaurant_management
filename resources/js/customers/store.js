import { overlayMixin, paginationMixin, money, formatDate, initials } from '../shared/kit.js';
import { CUSTOMERS, DISCOUNT_LOG, OPERATOR, TAGS, VENUE } from './demo-data.js';

export default function customersApp() {
    return {
        ...overlayMixin(),
        ...paginationMixin(10),
        venue: VENUE,
        operator: OPERATOR,
        tags: TAGS,
        money,
        formatDate,
        initials,

        customers: CUSTOMERS.map((c) => ({ ...c, tags: [...c.tags], favoriteItems: [...c.favoriteItems], recentOrders: [...c.recentOrders] })),
        discountLog: DISCOUNT_LOG,
        loading: false,
        query: '',
        segment: 'all',
        openRowMenu: null,
        activeId: null,
        activeTab: 'overview',
        draft: {},
        loyaltyDraft: { points: '', reason: '' },
        noteDraft: '',

        init() {
            this.loading = true;
            setTimeout(() => (this.loading = false), 500);
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
            return list.sort((a, b) => b.spend - a.spend);
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

        openProfile(c) {
            this.openRowMenu = null;
            this.activeId = c.id;
            this.activeTab = 'overview';
            this.open('profile');
        },
        openCreate() {
            this.openRowMenu = null;
            this.draft = { id: null, name: '', phone: '', email: '', birthday: '', anniversary: '', address: '', gstin: '', tags: [] };
            this.open('form');
        },
        openEdit(c) {
            this.openRowMenu = null;
            this.draft = { id: c.id, name: c.name, phone: c.phone, email: c.email, birthday: c.birthday, anniversary: c.anniversary, address: c.address, gstin: c.gstin, tags: [...c.tags] };
            this.swap('form');
        },
        toggleDraftTag(t) {
            const i = this.draft.tags.indexOf(t);
            i === -1 ? this.draft.tags.push(t) : this.draft.tags.splice(i, 1);
        },
        saveCustomer() {
            const d = this.draft;
            if (!d.name.trim() || d.phone.trim().length < 10) return;
            if (d.id) {
                Object.assign(this.customer(d.id), d);
                this.notify(`${d.name} updated`, 'success');
            } else {
                this.customers.unshift({
                    ...d, id: 'C' + (this.customers.length + 100), visits: 0, spend: 0, avgBill: 0, lastVisit: '—', points: 0, vip: false,
                    favoriteItems: [], recentOrders: [], reservationsCount: 0, notes: '', allergies: '', joinedDate: new Date().toISOString().slice(0, 10),
                });
                this.notify(`${d.name} added`, 'success');
            }
            this.closeAll();
        },
        toggleVip(c) {
            c.vip = !c.vip;
            this.notify(c.vip ? `${c.name} marked VIP` : `${c.name} removed from VIP`);
        },

        openLoyalty(c) {
            this.loyaltyDraft = { points: '', reason: '' };
            this.swap('loyalty');
        },
        applyLoyaltyAdjust(c) {
            const p = Number(this.loyaltyDraft.points);
            if (!p || !this.loyaltyDraft.reason) return;
            c.points = Math.max(0, c.points + p);
            (this.discountLog[c.id] ||= []).unshift({ at: this.formatDate(new Date()), text: `Manual adjustment: ${p > 0 ? '+' : ''}${p} pts — ${this.loyaltyDraft.reason}` });
            this.closeAll();
            this.notify(`Loyalty points ${p > 0 ? 'added' : 'deducted'} for ${c.name}`, 'success');
        },

        openNote(c) {
            this.noteDraft = c.notes || '';
            this.swap('note');
        },
        saveNote(c) {
            c.notes = this.noteDraft;
            this.closeAll();
            this.notify('Note saved', 'success');
        },
    };
}
