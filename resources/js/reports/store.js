import { money, formatDate } from '../shared/kit.js';
import { CATEGORIES, DAILY_SALES, MENU_PROFITABILITY, REVENUE_SUMMARY, VENUE, WAITER_SALES } from './demo-data.js';

const FLAGSHIP = new Set(['Daily Sales', 'Menu Profitability', 'Revenue', 'Sales by Waiter', 'Orders by Waiter']);
const boot = window.reportsModule || {};
const routes = window.reportsRoutes || {};

export default function reportsApp() {
    return {
        venue: boot.venue || VENUE,
        categories: boot.categories || CATEGORIES,
        dailySales: boot.dailySales || DAILY_SALES,
        menuProfitability: boot.menuProfitability || MENU_PROFITABILITY,
        revenueSummary: boot.revenueSummary || REVENUE_SUMMARY,
        waiterSales: boot.waiterSales || WAITER_SALES,
        generic: boot.generic || { rows: [], totals: { total: 0, qty: 0, avg: 0, best: { label: '-' } } },
        money,
        formatDate,

        activeCategory: 'sales',
        activeReport: boot.report || 'Daily Sales',
        dateFrom: boot.dateFrom || new Date().toISOString().slice(0, 10),
        dateTo: boot.dateTo || new Date().toISOString().slice(0, 10),
        branch: boot.venue?.branch || 'Ichapur Main Branch',
        loading: false,

        selectReport(cat, report) {
            this.activeCategory = cat;
            this.activeReport = report;
            this.loadReport();
        },
        isFlagship(name) {
            return FLAGSHIP.has(name);
        },
        exportAs(kind) {
            const params = new URLSearchParams({ report: this.activeReport, from: this.dateFrom, to: this.dateTo });
            window.location.href = `${routes.export}/${kind}?${params.toString()}`;
        },
        applyPayload(data) {
            this.venue = data.venue || this.venue;
            this.categories = data.categories || this.categories;
            this.dailySales = data.dailySales || this.dailySales;
            this.menuProfitability = data.menuProfitability || this.menuProfitability;
            this.revenueSummary = data.revenueSummary || this.revenueSummary;
            this.waiterSales = data.waiterSales || this.waiterSales;
            this.generic = data.generic || this.generic;
            this.dateFrom = data.dateFrom || this.dateFrom;
            this.dateTo = data.dateTo || this.dateTo;
        },
        async loadReport() {
            if (!routes.data) return;
            this.loading = true;
            try {
                const params = new URLSearchParams({ report: this.activeReport, from: this.dateFrom, to: this.dateTo });
                const response = await fetch(`${routes.data}?${params.toString()}`, { headers: { Accept: 'application/json' } });
                const data = await response.json().catch(() => ({}));
                if (!response.ok) throw new Error(data.message || 'Unable to load report');
                this.applyPayload(data);
            } finally {
                this.loading = false;
            }
        },

        get maxHourly() {
            return Math.max(1, ...this.dailySales.hourly.map((h) => h.sales));
        },

        get menuRows() {
            return this.menuProfitability.map((r) => ({
                ...r,
                margin: r.price - r.foodCost,
                foodCostPct: r.price ? Math.round((r.foodCost / r.price) * 100) : 0,
                totalContribution: (r.price - r.foodCost) * r.qtySold,
            }));
        },
        get quadrantMedians() {
            const rows = this.menuRows;
            if (!rows.length) return { qty: 0, margin: 0 };
            const sortedQty = [...rows].sort((a, b) => a.qtySold - b.qtySold);
            const sortedMargin = [...rows].sort((a, b) => a.margin - b.margin);
            const mid = (arr) => arr[Math.floor(arr.length / 2)];
            return { qty: mid(sortedQty).qtySold, margin: mid(sortedMargin).margin };
        },
        quadrantOf(r) {
            const { qty, margin } = this.quadrantMedians;
            const highPop = r.qtySold >= qty;
            const highMargin = r.margin >= margin;
            if (highPop && highMargin) return 'Star';
            if (highPop && !highMargin) return 'Plowhorse';
            if (!highPop && highMargin) return 'Puzzle';
            return 'Dog';
        },
        quadrantClass(q) {
            return { Star: 'border-emerald-400 bg-emerald-50 text-emerald-800', Plowhorse: 'border-sky-300 bg-sky-50 text-sky-800', Puzzle: 'border-violet-300 bg-violet-50 text-violet-800', Dog: 'border-slate-300 bg-slate-100 text-slate-500' }[q];
        },

        get genericRows() {
            return this.generic.rows || [];
        },
        get genericTotals() {
            return this.generic.totals || { total: 0, qty: 0, avg: 0, best: { label: '-' } };
        },
    };
}
