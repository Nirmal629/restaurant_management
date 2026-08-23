import { overlayMixin, paginationMixin, money } from '../shared/kit.js';
import { CATEGORIES, INGREDIENTS, LEDGER, RECIPES, STOCK_COUNTS, STORAGE_LOCATIONS, SUPPLIERS, TX_TYPES, UNITS, VENUE, WASTAGE, WASTAGE_REASONS } from './demo-data.js';

export default function inventoryApp() {
    return {
        ...overlayMixin(),
        ...paginationMixin(10),
        venue: VENUE,
        units: UNITS,
        categories: CATEGORIES,
        locations: STORAGE_LOCATIONS,
        suppliers: SUPPLIERS,
        txTypes: TX_TYPES,
        wastageReasons: WASTAGE_REASONS,
        recipes: RECIPES,
        money,

        tab: 'stock', // stock | ingredients | ledger | wastage | count | low
        ingredients: INGREDIENTS.map((i) => ({ ...i })),
        ledger: LEDGER.map((l) => ({ ...l })),
        wastage: WASTAGE.map((w) => ({ ...w })),
        stockCounts: STOCK_COUNTS.map((s) => ({ ...s, lines: s.lines.map((l) => ({ ...l })) })),

        query: '',
        categoryFilter: 'all',
        openRowMenu: null,
        activeRecipeItem: null,
        ingredientDraft: {},
        wastageDraft: {},
        adjustDraft: {},
        countDraft: null,

        statusLabel(s) {
            return { in: 'In Stock', low: 'Low Stock', out: 'Out of Stock' }[s] || s;
        },
        statusClass(s) {
            return { in: 'border-emerald-400 bg-emerald-50 text-emerald-800', low: 'border-amber-400 bg-amber-50 text-amber-800', out: 'border-rose-400 bg-rose-100 text-rose-800' }[s] || 'border-slate-300 bg-slate-100 text-slate-600';
        },
        recompute(i) {
            i.status = i.current <= 0 ? 'out' : i.current < i.min ? 'low' : 'in';
        },

        get summary() {
            return {
                total: this.ingredients.length,
                low: this.ingredients.filter((i) => i.status === 'low').length,
                out: this.ingredients.filter((i) => i.status === 'out').length,
                value: this.ingredients.reduce((s, i) => s + i.current * i.avgCost, 0),
                consumedToday: this.ledger.filter((l) => l.type === 'CONSUMPTION' && l.at.startsWith('23/08')).reduce((s, l) => s + Math.abs(l.change), 0),
                wastageToday: this.wastage.filter((w) => w.date === '23/08/2026').reduce((s, w) => s + w.cost, 0),
            };
        },

        get filteredIngredients() {
            let list = [...this.ingredients];
            if (this.categoryFilter !== 'all') list = list.filter((i) => i.category === this.categoryFilter);
            if (this.query.trim()) {
                const q = this.query.trim().toLowerCase();
                list = list.filter((i) => [i.name, i.id].join(' ').toLowerCase().includes(q));
            }
            return list;
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

        ingredient(id) {
            return this.ingredients.find((i) => i.id === id);
        },

        openIngredientForm(i = null) {
            this.openRowMenu = null;
            this.ingredientDraft = i ? { ...i } : { id: null, name: '', category: 'Grains & Rice', unit: 'KG', current: 0, min: 0, reorder: 0, avgCost: 0, supplier: this.suppliers[0], location: this.locations[0] };
            this.open('ingredientForm');
        },
        saveIngredient() {
            const d = this.ingredientDraft;
            if (!d.name.trim()) return;
            if (d.id) {
                Object.assign(this.ingredient(d.id), d);
                this.recompute(this.ingredient(d.id));
                this.notify(`${d.name} updated`, 'success');
            } else {
                const i = { ...d, id: 'ING-' + (100 + this.ingredients.length) };
                this.recompute(i);
                this.ingredients.unshift(i);
                this.notify(`${d.name} added`, 'success');
            }
            this.closeAll();
        },

        openAdjust(i) {
            this.openRowMenu = null;
            this.adjustDraft = { id: i.id, type: 'ADJUSTMENT', qty: '', reason: '' };
            this.open('adjust');
        },
        confirmAdjust() {
            const i = this.ingredient(this.adjustDraft.id);
            const qty = Number(this.adjustDraft.qty);
            if (!i || !qty) return;
            const prev = i.current;
            i.current = Math.max(0, i.current + qty);
            this.recompute(i);
            this.ledger.unshift({ at: 'Just now', ingredient: i.name, type: this.adjustDraft.type, ref: this.adjustDraft.reason || '—', prev, change: qty, next: i.current, user: 'Sourav Roy' });
            this.closeAll();
            this.notify(`${i.name} stock adjusted`, 'success');
        },

        openLedgerFor(i) {
            this.openRowMenu = null;
            this.tab = 'ledger';
            this.query = i.name;
        },
        get filteredLedger() {
            if (!this.query.trim()) return this.ledger;
            const q = this.query.trim().toLowerCase();
            return this.ledger.filter((l) => l.ingredient.toLowerCase().includes(q));
        },

        /** Displays a fractional KG/LITRE recipe quantity in the friendlier g/ml scale below 1 unit. */
        recipeQtyLabel(l) {
            if (l.unit === 'KG' && l.qty < 1) return Math.round(l.qty * 1000) + 'g';
            if (l.unit === 'LITRE' && l.qty < 1) return Math.round(l.qty * 1000) + 'ml';
            return l.qty + ' ' + l.unit;
        },
        openRecipe(itemName) {
            this.activeRecipeItem = itemName;
            this.open('recipe');
        },
        get recipeCost() {
            const r = this.recipes[this.activeRecipeItem];
            if (!r) return 0;
            return r.lines.reduce((s, l) => {
                const ing = this.ingredients.find((i) => i.name === l.ingredient);
                if (!ing) return s;
                const unitCost = ing.unit === l.unit ? ing.avgCost : ing.avgCost; // same-unit assumption for this demo
                return s + unitCost * l.qty;
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
        saveWastage() {
            const d = this.wastageDraft;
            const qty = Number(d.qty);
            if (!qty || !d.reason) return;
            const ing = this.ingredients.find((i) => i.name === d.ingredient);
            const cost = ing ? Math.round(ing.avgCost * qty) : 0;
            this.wastage.unshift({ id: 'WST-' + (100 + this.wastage.length), ingredient: d.ingredient, qty, unit: ing?.unit || '', reason: d.reason, cost, employee: d.employee, date: '23/08/2026', notes: d.notes });
            if (ing) {
                const prev = ing.current;
                ing.current = Math.max(0, ing.current - qty);
                this.recompute(ing);
                this.ledger.unshift({ at: 'Just now', ingredient: ing.name, type: 'WASTAGE', ref: 'WST-' + (100 + this.wastage.length - 1), prev, change: -qty, next: ing.current, user: d.employee });
            }
            this.closeAll();
            this.notify('Wastage recorded', 'warn');
        },

        openStockCount() {
            this.countDraft = { id: 'SC-2026-' + (5 + this.stockCounts.length), date: '23/08/2026', status: 'draft', by: 'Sourav Roy', lines: this.ingredients.map((i) => ({ ingredient: i.name, system: i.current, physical: i.current, reason: '' })) };
            this.open('count');
        },
        varianceOf(l) {
            return l.physical - l.system;
        },
        varianceValueOf(l) {
            const ing = this.ingredients.find((i) => i.name === l.ingredient);
            return this.varianceOf(l) * (ing?.avgCost || 0);
        },
        submitStockCount() {
            this.countDraft.status = 'completed';
            this.stockCounts.unshift(this.countDraft);
            this.countDraft.lines.forEach((l) => {
                if (this.varianceOf(l) !== 0) {
                    const ing = this.ingredients.find((i) => i.name === l.ingredient);
                    if (ing) {
                        const prev = ing.current;
                        ing.current = l.physical;
                        this.recompute(ing);
                        this.ledger.unshift({ at: 'Just now', ingredient: ing.name, type: 'ADJUSTMENT', ref: this.countDraft.id, prev, change: this.varianceOf(l), next: ing.current, user: 'Sourav Roy' });
                    }
                }
            });
            this.closeAll();
            this.notify('Stock count submitted — variances applied', 'success');
        },
    };
}
