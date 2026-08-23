import { overlayMixin, paginationMixin, money } from '../shared/kit.js';
import { CATEGORIES, INGREDIENTS, LEDGER, RECIPES, STOCK_COUNTS, STORAGE_LOCATIONS, SUPPLIERS, TX_TYPES, UNITS, VENUE, WASTAGE, WASTAGE_REASONS } from './demo-data.js';

export default function inventoryApp() {
    const boot = window.inventoryModule || {};
    const routes = window.inventoryRoutes || {};

    return {
        ...overlayMixin(),
        ...paginationMixin(10),
        venue: boot.venue || VENUE,
        units: boot.units || UNITS,
        categories: boot.categories?.length ? boot.categories : CATEGORIES,
        locations: boot.locations?.length ? boot.locations : STORAGE_LOCATIONS,
        suppliers: boot.suppliers?.length ? boot.suppliers : SUPPLIERS,
        txTypes: boot.txTypes || TX_TYPES,
        wastageReasons: boot.wastageReasons || WASTAGE_REASONS,
        recipes: boot.recipes || RECIPES,
        menuItems: boot.menuItems || [],
        money,
        routes,

        tab: 'stock',
        ingredients: (boot.ingredients || INGREDIENTS).map((i) => ({ ...i })),
        supplierRecords: (boot.supplierRecords || []).map((s) => ({ ...s, items: [...(s.items || [])] })),
        ledger: (boot.ledger || LEDGER).map((l) => ({ ...l })),
        wastage: (boot.wastage || WASTAGE).map((w) => ({ ...w })),
        stockCounts: (boot.stockCounts || STOCK_COUNTS).map((s) => ({ ...s, lines: (s.lines || []).map((l) => ({ ...l })) })),

        query: '',
        categoryFilter: 'all',
        supplierStatusFilter: 'all',
        openRowMenu: null,
        activeRecipeItem: null,
        activeSupplierId: null,
        saving: false,
        showIngredientForm: false,
        showAdjust: false,
        showRecipe: false,
        showRecipeForm: false,
        showWastageForm: false,
        showCount: false,
        showSupplierDetail: false,
        showSupplierForm: false,
        ingredientDraft: { name: '' },
        supplierDraft: {},
        wastageDraft: {},
        adjustDraft: {},
        countDraft: null,
        recipeDraft: { itemName: '', itemId: null, lines: [] },

        open(name) {
            this.stack = [name];
            this.syncOverlayFlags();
            this.$nextTick(() => this.focusFirst());
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
            this.showIngredientForm = this.stack.includes('ingredientForm');
            this.showAdjust = this.stack.includes('adjust');
            this.showRecipe = this.stack.includes('recipe');
            this.showRecipeForm = this.stack.includes('recipeForm');
            this.showWastageForm = this.stack.includes('wastageForm');
            this.showCount = this.stack.includes('count');
            this.showSupplierDetail = this.stack.includes('supplierDetail');
            this.showSupplierForm = this.stack.includes('supplierForm');
        },
        cleanupOverlayState() {
            this.syncOverlayFlags();
            if (!this.showIngredientForm) this.ingredientDraft = { name: '' };
            if (!this.showAdjust) this.adjustDraft = {};
            if (!this.showRecipe) this.activeRecipeItem = null;
            if (!this.showRecipeForm) this.recipeDraft = { itemName: '', itemId: null, lines: [] };
            if (!this.showWastageForm) this.wastageDraft = {};
            if (!this.showCount) this.countDraft = null;
            if (!this.showSupplierDetail) this.activeSupplierId = null;
            if (!this.showSupplierForm) this.supplierDraft = {};
            this.openRowMenu = null;
        },

        statusLabel(s) {
            return { in: 'In Stock', low: 'Low Stock', out: 'Out of Stock' }[s] || s;
        },
        statusClass(s) {
            return { in: 'border-emerald-400 bg-emerald-50 text-emerald-800', low: 'border-amber-400 bg-amber-50 text-amber-800', out: 'border-rose-400 bg-rose-100 text-rose-800' }[s] || 'border-slate-300 bg-slate-100 text-slate-600';
        },

        get summary() {
            const today = new Date().toLocaleDateString('en-GB');
            return {
                total: this.ingredients.length,
                low: this.ingredients.filter((i) => i.status === 'low').length,
                out: this.ingredients.filter((i) => i.status === 'out').length,
                value: this.ingredients.reduce((s, i) => s + Number(i.current || 0) * Number(i.avgCost || 0), 0),
                consumedToday: this.ledger.filter((l) => l.type === 'CONSUMPTION' && String(l.at || '').startsWith(today)).reduce((s, l) => s + Math.abs(Number(l.change || 0)), 0),
                wastageToday: this.wastage.filter((w) => w.date === today).reduce((s, w) => s + Number(w.cost || 0), 0),
            };
        },

        get filteredIngredients() {
            let list = [...this.ingredients];
            if (this.categoryFilter !== 'all') list = list.filter((i) => i.category === this.categoryFilter);
            if (this.query.trim()) {
                const q = this.query.trim().toLowerCase();
                list = list.filter((i) => [i.name, i.code, i.id].join(' ').toLowerCase().includes(q));
            }
            return list.sort((a, b) => Number(b.id) - Number(a.id));
        },
        get pagedIngredients() {
            return this.pageSlice(this.filteredIngredients);
        },
        get lowStockList() {
            return this.ingredients.filter((i) => i.status === 'low' || i.status === 'out');
        },
        clearFilters() {
            this.query = '';
            this.categoryFilter = 'all';
            this.page = 1;
        },
        clearSupplierFilters() {
            this.query = '';
            this.supplierStatusFilter = 'all';
            this.page = 1;
        },

        ingredient(id) {
            return this.ingredients.find((i) => Number(i.id) === Number(id));
        },
        supplier(id) {
            return this.supplierRecords.find((s) => Number(s.id) === Number(id));
        },
        get activeSupplier() {
            return this.supplier(this.activeSupplierId);
        },
        get filteredSuppliers() {
            let list = [...this.supplierRecords];
            if (this.supplierStatusFilter !== 'all') list = list.filter((s) => s.status === this.supplierStatusFilter);
            if (this.query.trim()) {
                const q = this.query.trim().toLowerCase();
                list = list.filter((s) => [s.name, s.contact, s.phone, s.email, s.gstin].join(' ').toLowerCase().includes(q));
            }
            return list;
        },
        get pagedSuppliers() {
            return this.pageSlice(this.filteredSuppliers);
        },

        openIngredientForm(i = null) {
            this.openRowMenu = null;
            this.ingredientDraft = i ? { ...i } : { id: null, name: '', category: this.categories[0] || 'General', unit: this.units[0] || 'KG', current: 0, min: 0, reorder: 0, avgCost: 0, supplier: this.suppliers[0] || '', location: this.locations[0] || '' };
            this.open('ingredientForm');
        },
        async saveIngredient() {
            const d = this.ingredientDraft;
            if (!d.name.trim()) return;
            const result = await this.request(d.id ? `${routes.ingredients}/${d.id}` : routes.ingredients, {
                method: d.id ? 'PUT' : 'POST',
                body: JSON.stringify(this.ingredientPayload(d)),
            });
            if (!result) return;
            this.replaceIngredient(result.ingredient);
            if (result.ledger) this.ledger = result.ledger;
            this.refreshPicklists();
            this.notify(result.message || 'Ingredient saved', 'success');
            this.closeAll();
        },
        async deleteIngredient(i) {
            if (!confirm(`Delete ${i.name}?`)) return;
            const result = await this.request(`${routes.ingredients}/${i.id}`, { method: 'DELETE' });
            if (!result) return;
            this.ingredients = result.ingredients || this.ingredients.filter((row) => Number(row.id) !== Number(i.id));
            if (result.ledger) this.ledger = result.ledger;
            this.notify(result.message || `${i.name} deleted`, 'warn');
        },
        ingredientPayload(d) {
            return { name: d.name, category: d.category, unit: d.unit, current: d.current || 0, min: d.min || 0, reorder: d.reorder || 0, avgCost: d.avgCost || 0, supplier: d.supplier || null, location: d.location || null };
        },

        openSupplierDetail(s) {
            this.openRowMenu = null;
            this.activeSupplierId = s.id;
            this.open('supplierDetail');
        },
        openSupplierForm(s = null) {
            this.openRowMenu = null;
            this.supplierDraft = s ? { ...s, items: [...(s.items || [])] } : { id: null, name: '', contact: '', phone: '', email: '', gstin: '', address: '', outstanding: 0, status: 'active', items: [] };
            this.open('supplierForm');
        },
        async saveSupplier() {
            const d = this.supplierDraft;
            if (!d.name?.trim()) return;
            const result = await this.request(d.id ? `${routes.suppliers}/${d.id}` : routes.suppliers, {
                method: d.id ? 'PUT' : 'POST',
                body: JSON.stringify(this.supplierPayload(d)),
            });
            if (!result) return;
            this.supplierRecords = result.suppliers || this.upsertSupplier(result.supplier);
            this.suppliers = result.supplierNames || this.suppliers;
            if (result.ingredients) this.ingredients = result.ingredients;
            this.notify(result.message || 'Supplier saved', 'success');
            this.closeAll();
        },
        async deleteSupplier(s) {
            if (!confirm(`Delete ${s.name}?`)) return;
            const result = await this.request(`${routes.suppliers}/${s.id}`, { method: 'DELETE' });
            if (!result) return;
            this.supplierRecords = result.suppliers || this.supplierRecords.filter((row) => Number(row.id) !== Number(s.id));
            this.suppliers = result.supplierNames || this.suppliers.filter((name) => name !== s.name);
            this.notify(result.message || `${s.name} deleted`, 'warn');
            this.closeAll();
        },
        supplierPayload(d) {
            return { name: d.name, contact: d.contact || null, phone: d.phone || null, email: d.email || null, gstin: d.gstin || null, address: d.address || null, outstanding: d.outstanding || 0, status: d.status || 'active' };
        },
        upsertSupplier(supplier) {
            const records = [...this.supplierRecords];
            const index = records.findIndex((s) => Number(s.id) === Number(supplier.id));
            if (index >= 0) records.splice(index, 1, supplier);
            else records.unshift(supplier);
            return records;
        },

        openAdjust(i) {
            this.openRowMenu = null;
            this.adjustDraft = { id: i.id, type: 'ADJUSTMENT', qty: '', reason: '' };
            this.open('adjust');
        },
        async confirmAdjust() {
            const i = this.ingredient(this.adjustDraft.id);
            const qty = Number(this.adjustDraft.qty);
            if (!i || !qty) return;
            const result = await this.request(`${routes.ingredients}/${i.id}/adjust`, {
                method: 'PATCH',
                body: JSON.stringify({ type: this.adjustDraft.type, qty, reason: this.adjustDraft.reason || null }),
            });
            if (!result) return;
            this.replaceIngredient(result.ingredient);
            if (result.ledger) this.ledger = result.ledger;
            this.notify(result.message || `${i.name} stock adjusted`, 'success');
            this.closeAll();
        },

        openLedgerFor(i) {
            this.openRowMenu = null;
            this.tab = 'ledger';
            this.query = i.name;
        },
        get filteredLedger() {
            if (!this.query.trim()) return this.ledger;
            const q = this.query.trim().toLowerCase();
            return this.ledger.filter((l) => String(l.ingredient || '').toLowerCase().includes(q));
        },

        recipeQtyLabel(l) {
            if (l.unit === 'KG' && l.qty < 1) return Math.round(l.qty * 1000) + 'g';
            if (l.unit === 'LITRE' && l.qty < 1) return Math.round(l.qty * 1000) + 'ml';
            return l.qty + ' ' + l.unit;
        },
        openRecipe(itemName) {
            this.activeRecipeItem = itemName;
            this.open('recipe');
        },
        openRecipeForm(itemName = this.activeRecipeItem) {
            const item = itemName ? this.menuItems.find((m) => m.name === itemName) : this.menuItems.find((m) => !this.recipes[m.name]) || this.menuItems[0];
            const name = item?.name || itemName || '';
            this.recipeDraft = { itemName: name, itemId: item?.id || null, lines: (this.recipes[name]?.lines || []).map((l) => ({ ...l })) };
            if (!this.recipeDraft.lines.length) this.addRecipeLine();
            this.open('recipeForm');
        },
        selectRecipeItem() {
            const item = this.menuItems.find((m) => Number(m.id) === Number(this.recipeDraft.itemId));
            this.recipeDraft.itemName = item?.name || '';
            this.recipeDraft.lines = (this.recipes[this.recipeDraft.itemName]?.lines || []).map((l) => ({ ...l }));
            if (!this.recipeDraft.lines.length) this.addRecipeLine();
        },
        addRecipeLine() {
            this.recipeDraft.lines.push({ ingredient: this.ingredients[0]?.name || '', qty: '', unit: this.ingredients[0]?.unit || this.units[0] || 'KG' });
        },
        removeRecipeLine(index) {
            this.recipeDraft.lines.splice(index, 1);
        },
        async saveRecipe() {
            if (!this.recipeDraft.itemId) return;
            const result = await this.request(`${routes.recipes}/${this.recipeDraft.itemId}`, {
                method: 'PUT',
                body: JSON.stringify({ lines: this.recipeDraft.lines.filter((l) => l.ingredient && l.qty) }),
            });
            if (!result) return;
            this.recipes = result.recipes || this.recipes;
            this.activeRecipeItem = this.recipeDraft.itemName;
            this.notify(result.message || 'Recipe saved', 'success');
            this.closeAll();
        },
        get recipeCost() {
            const r = this.recipes[this.activeRecipeItem];
            if (!r) return 0;
            return r.lines.reduce((s, l) => {
                const ing = this.ingredients.find((i) => i.name === l.ingredient);
                return ing ? s + Number(ing.avgCost || 0) * Number(l.qty || 0) : s;
            }, 0);
        },
        get recipeFoodCostPct() {
            const r = this.recipes[this.activeRecipeItem];
            return r ? Math.round((this.recipeCost / r.sellPrice) * 100) : 0;
        },

        openWastageForm() {
            this.wastageDraft = { ingredient: this.ingredients[0]?.name, qty: '', reason: '', employee: 'Chef Imran', notes: '' };
            this.open('wastageForm');
        },
        async saveWastage() {
            const d = this.wastageDraft;
            const qty = Number(d.qty);
            if (!qty || !d.reason) return;
            const result = await this.request(routes.wastage, { method: 'POST', body: JSON.stringify({ ingredient: d.ingredient, qty, reason: d.reason, employee: d.employee || null, notes: d.notes || null }) });
            if (!result) return;
            this.replaceIngredient(result.ingredient);
            if (result.ledger) this.ledger = result.ledger;
            if (result.wastage) this.wastage = result.wastage;
            this.notify(result.message || 'Wastage recorded', 'warn');
            this.closeAll();
        },
        async deleteWastage(w) {
            if (!confirm(`Delete ${w.id}?`)) return;
            const result = await this.request(`${routes.wastageBase}/${w.id}`, { method: 'DELETE' });
            if (!result) return;
            this.wastage = result.wastage || this.wastage.filter((row) => row.id !== w.id);
            this.notify(result.message || `${w.id} deleted`, 'warn');
        },

        openStockCount() {
            this.countDraft = { lines: this.ingredients.map((i) => ({ ingredient: i.name, system: Number(i.current), physical: Number(i.current), reason: '' })) };
            this.open('count');
        },
        varianceOf(l) {
            return Number(l.physical || 0) - Number(l.system || 0);
        },
        varianceValueOf(l) {
            const ing = this.ingredients.find((i) => i.name === l.ingredient);
            return this.varianceOf(l) * Number(ing?.avgCost || 0);
        },
        async submitStockCount() {
            if (!this.countDraft?.lines?.length) return;
            const result = await this.request(routes.counts, { method: 'POST', body: JSON.stringify({ lines: this.countDraft.lines }) });
            if (!result) return;
            this.ingredients = result.ingredients || this.ingredients;
            this.ledger = result.ledger || this.ledger;
            this.stockCounts = result.stockCounts || this.stockCounts;
            this.notify(result.message || 'Stock count submitted', 'success');
            this.closeAll();
        },
        async deleteStockCount(s) {
            if (!confirm(`Delete ${s.id}?`)) return;
            const result = await this.request(`${routes.countsBase}/${s.id}`, { method: 'DELETE' });
            if (!result) return;
            this.stockCounts = result.stockCounts || this.stockCounts.filter((row) => row.id !== s.id);
            this.notify(result.message || `${s.id} deleted`, 'warn');
        },
        printInventory() {
            window.print();
        },
        poUrlFor(i) {
            const qty = Math.max(0, Number(i.reorder || 0) - Number(i.current || 0));
            const params = new URLSearchParams({ ingredient: i.name, supplier: i.supplier || '', qty: String(qty || i.reorder || 1), unit: i.unit });
            return `/purchases?${params.toString()}`;
        },

        replaceIngredient(ingredient) {
            const index = this.ingredients.findIndex((i) => Number(i.id) === Number(ingredient.id));
            if (index >= 0) this.ingredients.splice(index, 1, ingredient);
            else this.ingredients.unshift(ingredient);
        },
        refreshPicklists() {
            this.categories = [...new Set([...this.categories, ...this.ingredients.map((i) => i.category).filter(Boolean)])].sort();
            this.locations = [...new Set([...this.locations, ...this.ingredients.map((i) => i.location).filter(Boolean)])].sort();
            this.suppliers = [...new Set([...this.suppliers, ...this.ingredients.map((i) => i.supplier).filter(Boolean)])].sort();
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
                    this.notify(firstError || data.message || 'Inventory update failed', 'warn');
                    return null;
                }
                return data;
            } catch (error) {
                this.notify('Network error while saving inventory', 'warn');
                return null;
            } finally {
                this.saving = false;
            }
        },
    };
}
