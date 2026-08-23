import { overlayMixin, paginationMixin, money } from '../shared/kit.js';
import { CATEGORIES, COMBOS, ITEMS, MODIFIER_GROUPS, STATIONS, TAX_PROFILES, VENUE } from './demo-data.js';

export default function menuApp() {
    return {
        ...overlayMixin(),
        ...paginationMixin(10),
        venue: VENUE,
        categories: CATEGORIES,
        stations: STATIONS,
        taxProfiles: TAX_PROFILES,
        money,

        tab: 'items', // items | categories | modifiers | stations | combos
        items: ITEMS.map((i) => ({ ...i, variants: [...i.variants], modifierGroupIds: [...i.modifierGroupIds] })),
        modifierGroups: MODIFIER_GROUPS.map((g) => ({ ...g, options: g.options.map((o) => ({ ...o })) })),
        combos: COMBOS.map((c) => ({ ...c, items: [...c.items] })),

        query: '',
        categoryFilter: 'all',
        dietFilter: 'all',
        openRowMenu: null,
        activeId: null,
        itemDraft: {},
        categoryDraft: { key: null, label: '' },
        modifierDraft: {},
        comboDraft: {},

        categoryLabel(k) {
            return this.categories.find((c) => c.key === k)?.label || k;
        },
        stationLabel(k) {
            return this.stations.find((s) => s.key === k)?.label || k;
        },
        itemCountFor(catKey) {
            return this.items.filter((i) => i.category === catKey).length;
        },
        stationCountFor(key) {
            return this.items.filter((i) => i.station === key).length;
        },

        availabilityLabel(s) {
            return { available: 'Available', sold_out: 'Sold Out', temp_unavailable: 'Temp. Unavailable' }[s] || s;
        },
        availabilityClass(s) {
            return {
                available: 'border-emerald-400 bg-emerald-50 text-emerald-800',
                sold_out: 'border-rose-300 bg-rose-50 text-rose-700',
                temp_unavailable: 'border-amber-400 bg-amber-50 text-amber-800',
            }[s] || 'border-slate-300 bg-slate-100 text-slate-600';
        },
        cycleAvailability(i) {
            const order = ['available', 'sold_out', 'temp_unavailable'];
            i.availability = order[(order.indexOf(i.availability) + 1) % order.length];
        },

        get filteredItems() {
            let list = [...this.items];
            if (this.categoryFilter !== 'all') list = list.filter((i) => i.category === this.categoryFilter);
            if (this.dietFilter !== 'all') list = list.filter((i) => i.dietType === this.dietFilter);
            if (this.query.trim()) {
                const q = this.query.trim().toLowerCase();
                list = list.filter((i) => [i.name, i.sku].join(' ').toLowerCase().includes(q));
            }
            return list;
        },
        get pagedItems() {
            return this.pageSlice(this.filteredItems);
        },
        clearFilters() {
            this.query = '';
            this.categoryFilter = 'all';
            this.dietFilter = 'all';
            this.page = 1;
        },

        item(id) {
            return this.items.find((i) => i.id === id);
        },
        get activeItem() {
            return this.item(this.activeId);
        },

        openItemForm(i = null) {
            this.openRowMenu = null;
            this.itemDraft = i
                ? { ...i, variants: i.variants.map((v) => ({ ...v })), modifierGroupIds: [...i.modifierGroupIds] }
                : { id: null, sku: '', name: '', shortName: '', category: 'starters', dietType: 'veg', price: '', taxProfile: 'GST 5%', prepTime: '', station: 'main', description: '', featured: false, popular: false, stockTracked: false, availability: 'available', variants: [], modifierGroupIds: [] };
            this.open('itemForm');
        },
        addVariant() {
            this.itemDraft.variants.push({ label: '', price: '' });
        },
        removeVariant(i) {
            this.itemDraft.variants.splice(i, 1);
        },
        toggleDraftModifier(id) {
            const i = this.itemDraft.modifierGroupIds.indexOf(id);
            i === -1 ? this.itemDraft.modifierGroupIds.push(id) : this.itemDraft.modifierGroupIds.splice(i, 1);
        },
        saveItem() {
            const d = this.itemDraft;
            if (!d.name.trim() || !d.sku.trim() || !d.price) return;
            if (d.id) {
                Object.assign(this.item(d.id), d);
                this.notify(`${d.name} updated`, 'success');
            } else {
                this.items.unshift({ ...d, id: 'ITM' + (this.items.length + 100) });
                this.notify(`${d.name} added`, 'success');
            }
            this.closeAll();
        },
        openViewItem(i) {
            this.openRowMenu = null;
            this.activeId = i.id;
            this.open('itemView');
        },
        duplicateItem(i) {
            this.openRowMenu = null;
            const copy = { ...i, id: 'ITM' + (this.items.length + 100), name: i.name + ' (Copy)', sku: i.sku + '-COPY' };
            this.items.unshift(copy);
            this.notify(`${i.name} duplicated`, 'success');
        },

        /* Categories */
        openCategoryForm(c = null) {
            this.categoryDraft = c ? { ...c } : { key: null, label: '' };
            this.open('categoryForm');
        },
        saveCategory() {
            const d = this.categoryDraft;
            if (!d.label.trim()) return;
            if (d.key) {
                const c = this.categories.find((x) => x.key === d.key);
                c.label = d.label;
            } else {
                this.categories.push({ key: d.label.toLowerCase().replace(/\s+/g, '-'), label: d.label });
            }
            this.closeAll();
            this.notify('Category saved', 'success');
        },

        /* Modifiers */
        openModifierForm(g = null) {
            this.modifierDraft = g ? { ...g, options: g.options.map((o) => ({ ...o })) } : { id: null, label: '', type: 'single', required: true, min: 1, max: 1, options: [{ label: '', delta: 0 }] };
            this.open('modifierForm');
        },
        addModifierOption() {
            this.modifierDraft.options.push({ label: '', delta: 0 });
        },
        removeModifierOption(i) {
            this.modifierDraft.options.splice(i, 1);
        },
        saveModifier() {
            const d = this.modifierDraft;
            if (!d.label.trim()) return;
            if (d.id) {
                Object.assign(this.modifierGroups.find((g) => g.id === d.id), d);
            } else {
                this.modifierGroups.push({ ...d, id: 'mg' + (this.modifierGroups.length + 1) });
            }
            this.closeAll();
            this.notify('Modifier group saved', 'success');
        },

        /* Combos */
        openComboForm(c = null) {
            this.comboDraft = c ? { ...c, items: c.items.map((i) => ({ ...i })) } : { id: null, name: '', price: '', items: [{ name: '', qty: 1 }] };
            this.open('comboForm');
        },
        addComboLine() {
            this.comboDraft.items.push({ name: '', qty: 1 });
        },
        removeComboLine(i) {
            this.comboDraft.items.splice(i, 1);
        },
        saveCombo() {
            const d = this.comboDraft;
            if (!d.name.trim() || !d.price) return;
            if (d.id) {
                Object.assign(this.combos.find((c) => c.id === d.id), d);
            } else {
                this.combos.push({ ...d, id: 'CB' + (this.combos.length + 1) });
            }
            this.closeAll();
            this.notify('Combo saved', 'success');
        },
    };
}
