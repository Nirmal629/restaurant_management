import { overlayMixin, paginationMixin, money } from '../shared/kit.js';
import { CATEGORIES, COMBOS, ITEMS, MODIFIER_GROUPS, STATIONS, TAX_PROFILES, VENUE } from './demo-data.js';

export default function menuApp() {
    const boot = window.menuModule || {};
    const routes = window.menuRoutes || {};
    const normalizeItem = (i) => ({ ...i, variants: [...(i.variants || [])], modifierGroupIds: [...(i.modifierGroupIds || [])] });
    const normalizeModifier = (g) => ({ ...g, options: (g.options || []).map((o) => ({ ...o })) });
    const normalizeCombo = (c) => ({ ...c, items: (c.items || []).map((i) => ({ ...i })) });

    return {
        ...overlayMixin(),
        ...paginationMixin(10),
        venue: boot.venue || VENUE,
        categories: boot.categories || CATEGORIES,
        stations: boot.stations || STATIONS,
        taxProfiles: boot.taxProfiles || TAX_PROFILES,
        money,

        tab: 'items',
        items: (boot.items || ITEMS).map(normalizeItem),
        modifierGroups: (boot.modifierGroups || MODIFIER_GROUPS).map(normalizeModifier),
        combos: (boot.combos || COMBOS).map(normalizeCombo),

        query: '',
        categoryFilter: 'all',
        dietFilter: 'all',
        openRowMenu: null,
        activeId: null,
        saving: false,
        itemFormMode: 'create',
        showItemForm: false,
        showItemView: false,
        showCategoryForm: false,
        showModifierForm: false,
        showComboForm: false,
        itemDraft: { variants: [], modifierGroupIds: [] },
        categoryDraft: { key: null, label: '' },
        modifierDraft: { options: [] },
        comboDraft: { items: [] },

        init() {
            this.sortCollections();
        },
        open(name) {
            this.stack = [name];
            this.syncOverlayFlags();
            this.$nextTick(() => this.focusFirst());
        },
        swap(name) {
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
            this.showItemForm = this.stack.includes('itemForm');
            this.showItemView = this.stack.includes('itemView');
            this.showCategoryForm = this.stack.includes('categoryForm');
            this.showModifierForm = this.stack.includes('modifierForm');
            this.showComboForm = this.stack.includes('comboForm');
        },
        cleanupOverlayState() {
            this.syncOverlayFlags();
            if (!this.showItemView) this.activeId = null;
            if (!this.showItemForm) {
                this.itemFormMode = 'create';
                this.itemDraft = { variants: [], modifierGroupIds: [] };
            }
            if (!this.showCategoryForm) this.categoryDraft = { key: null, label: '' };
            if (!this.showModifierForm) this.modifierDraft = { options: [] };
            if (!this.showComboForm) this.comboDraft = { items: [] };
            this.openRowMenu = null;
        },

        categoryLabel(k) {
            return this.categories.find((c) => Number(c.key) === Number(k))?.label || k;
        },
        stationLabel(k) {
            return this.stations.find((s) => Number(s.key) === Number(k))?.label || k;
        },
        itemCountFor(catKey) {
            return this.items.filter((i) => Number(i.category) === Number(catKey)).length;
        },
        stationCountFor(key) {
            return this.items.filter((i) => Number(i.station) === Number(key)).length;
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
        async cycleAvailability(i) {
            const order = ['available', 'sold_out', 'temp_unavailable'];
            const next = order[(order.indexOf(i.availability) + 1) % order.length];
            const result = await this.request(`${routes.items}/${i.id}/availability`, { method: 'PATCH', body: JSON.stringify({ availability: next }) });
            if (!result) return;
            this.replaceItem(result.item);
            this.notify(result.message || 'Availability updated', 'success');
        },

        get filteredItems() {
            let list = [...this.items];
            if (this.categoryFilter !== 'all') list = list.filter((i) => Number(i.category) === Number(this.categoryFilter));
            if (this.dietFilter !== 'all') list = list.filter((i) => i.dietType === this.dietFilter);
            if (this.query.trim()) {
                const q = this.query.trim().toLowerCase();
                list = list.filter((i) => [i.name, i.sku].join(' ').toLowerCase().includes(q));
            }
            return list.sort((a, b) => Number(b.id) - Number(a.id));
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
            return this.items.find((i) => Number(i.id) === Number(id));
        },
        get activeItem() {
            return this.item(this.activeId);
        },

        openItemForm(i = null) {
            this.openRowMenu = null;
            this.activeId = null;
            this.itemFormMode = i ? 'edit' : 'create';
            this.itemDraft = i
                ? { ...normalizeItem(i), variants: i.variants.map((v) => ({ ...v })), modifierGroupIds: [...i.modifierGroupIds] }
                : { id: null, sku: '', name: '', shortName: '', category: this.categories[0]?.key, dietType: 'veg', price: '', taxProfile: this.taxProfiles[0] || 'GST 5%', prepTime: 10, station: this.stations[0]?.key || null, description: '', featured: false, popular: false, stockTracked: false, availability: 'available', variants: [], modifierGroupIds: [] };
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
        async saveItem() {
            const d = this.itemDraft;
            if (!d.name.trim() || !d.sku.trim() || !d.price) return;
            const editing = this.itemFormMode === 'edit' && d.id;
            const result = await this.request(editing ? `${routes.items}/${d.id}` : routes.items, {
                method: editing ? 'PUT' : 'POST',
                body: JSON.stringify(this.itemPayload(d)),
            });
            if (!result) return;
            editing ? this.replaceItem(result.item) : this.items.unshift(normalizeItem(result.item));
            this.sortCollections();
            this.notify(result.message || `${d.name} saved`, 'success');
            this.closeAll();
        },
        openViewItem(i) {
            this.openRowMenu = null;
            this.activeId = i.id;
            this.open('itemView');
        },
        async duplicateItem(i) {
            this.openRowMenu = null;
            const result = await this.request(`${routes.items}/${i.id}/duplicate`, { method: 'POST' });
            if (!result) return;
            this.items.unshift(normalizeItem(result.item));
            this.sortCollections();
            this.notify(result.message || `${i.name} duplicated`, 'success');
        },
        itemPayload(d) {
            return {
                sku: d.sku,
                name: d.name,
                shortName: d.shortName || null,
                category: d.category,
                dietType: d.dietType,
                price: d.price,
                taxProfile: d.taxProfile,
                prepTime: d.prepTime || 0,
                station: d.station || null,
                description: d.description || null,
                featured: Boolean(d.featured),
                popular: Boolean(d.popular),
                stockTracked: Boolean(d.stockTracked),
                availability: d.availability,
                variants: (d.variants || []).filter((v) => v.label && v.price !== ''),
                modifierGroupIds: d.modifierGroupIds || [],
            };
        },

        openCategoryForm(c = null) {
            this.openRowMenu = null;
            this.categoryDraft = c ? { ...c } : { key: null, label: '' };
            this.open('categoryForm');
        },
        async saveCategory() {
            const d = this.categoryDraft;
            if (!d.label.trim()) return;
            const result = await this.request(d.key ? `${routes.categories}/${d.key}` : routes.categories, {
                method: d.key ? 'PUT' : 'POST',
                body: JSON.stringify({ name: d.label }),
            });
            if (!result) return;
            this.replaceCategory(result.category);
            this.notify(result.message || 'Category saved', 'success');
            this.closeAll();
        },

        openModifierForm(g = null) {
            this.openRowMenu = null;
            this.modifierDraft = g ? normalizeModifier(g) : { id: null, label: '', type: 'single', required: true, min: 1, max: 1, options: [{ label: '', delta: 0 }] };
            this.open('modifierForm');
        },
        addModifierOption() {
            this.modifierDraft.options.push({ label: '', delta: 0 });
        },
        removeModifierOption(i) {
            this.modifierDraft.options.splice(i, 1);
        },
        async saveModifier() {
            const d = this.modifierDraft;
            if (!d.label.trim()) return;
            const result = await this.request(d.id ? `${routes.modifiers}/${d.id}` : routes.modifiers, {
                method: d.id ? 'PUT' : 'POST',
                body: JSON.stringify({ label: d.label, type: d.type, required: Boolean(d.required), min: Number(d.min) || 0, max: Number(d.max) || 1, options: d.options.filter((o) => o.label) }),
            });
            if (!result) return;
            this.replaceModifier(result.modifierGroup);
            this.notify(result.message || 'Modifier group saved', 'success');
            this.closeAll();
        },

        openComboForm(c = null) {
            this.openRowMenu = null;
            this.comboDraft = c ? normalizeCombo(c) : { id: null, name: '', price: '', items: [{ name: '', qty: 1 }] };
            this.open('comboForm');
        },
        addComboLine() {
            this.comboDraft.items.push({ name: '', qty: 1 });
        },
        removeComboLine(i) {
            this.comboDraft.items.splice(i, 1);
        },
        async saveCombo() {
            const d = this.comboDraft;
            if (!d.name.trim() || !d.price) return;
            const result = await this.request(d.id ? `${routes.combos}/${d.id}` : routes.combos, {
                method: d.id ? 'PUT' : 'POST',
                body: JSON.stringify({ name: d.name, price: d.price, items: d.items.filter((i) => i.name && i.qty) }),
            });
            if (!result) return;
            this.replaceCombo(result.combo);
            this.notify(result.message || 'Combo saved', 'success');
            this.closeAll();
        },

        replaceItem(item) {
            const next = normalizeItem(item);
            const index = this.items.findIndex((i) => Number(i.id) === Number(next.id));
            if (index >= 0) this.items.splice(index, 1, next);
            else this.items.unshift(next);
        },
        replaceCategory(category) {
            const index = this.categories.findIndex((c) => Number(c.key) === Number(category.key));
            if (index >= 0) this.categories.splice(index, 1, category);
            else this.categories.push(category);
        },
        replaceModifier(group) {
            const next = normalizeModifier(group);
            const index = this.modifierGroups.findIndex((g) => Number(g.id) === Number(next.id));
            if (index >= 0) this.modifierGroups.splice(index, 1, next);
            else this.modifierGroups.unshift(next);
        },
        replaceCombo(combo) {
            const next = normalizeCombo(combo);
            const index = this.combos.findIndex((c) => Number(c.id) === Number(next.id));
            if (index >= 0) this.combos.splice(index, 1, next);
            else this.combos.unshift(next);
        },
        sortCollections() {
            this.items.sort((a, b) => Number(b.id) - Number(a.id));
            this.modifierGroups.sort((a, b) => Number(b.id) - Number(a.id));
            this.combos.sort((a, b) => Number(b.id) - Number(a.id));
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
                    this.notify(firstError || data.message || 'Menu update failed', 'warn');
                    return null;
                }
                return data;
            } catch (error) {
                this.notify('Network error while saving menu', 'warn');
                return null;
            } finally {
                this.saving = false;
            }
        },
    };
}
