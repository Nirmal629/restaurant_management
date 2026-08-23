import { money, formatDate } from '../shared/kit.js';
import { CATEGORIES, DAILY_SALES, MENU_PROFITABILITY, REVENUE_SUMMARY, VENUE, WAITER_SALES } from './demo-data.js';

/** Deterministic string hash → small PRNG, so "generic" reports are stable across renders/reloads. */
function seedFrom(str) {
    let h = 0;
    for (const ch of str) h = (h * 31 + ch.charCodeAt(0)) >>> 0;
    return () => ((h = (h * 1103515245 + 12345) >>> 0) / 4294967296);
}

const FLAGSHIP = new Set(['Daily Sales', 'Menu Profitability', 'Revenue', 'Sales by Waiter', 'Orders by Waiter']);

export default function reportsApp() {
    return {
        venue: VENUE,
        categories: CATEGORIES,
        dailySales: DAILY_SALES,
        menuProfitability: MENU_PROFITABILITY,
        revenueSummary: REVENUE_SUMMARY,
        waiterSales: WAITER_SALES,
        money,
        formatDate,

        activeCategory: 'sales',
        activeReport: 'Daily Sales',
        dateFrom: '2026-08-17',
        dateTo: '2026-08-23',
        branch: 'Ichapur Main Branch',

        selectReport(cat, report) {
            this.activeCategory = cat;
            this.activeReport = report;
        },
        isFlagship(name) {
            return FLAGSHIP.has(name);
        },
        exportAs(kind) {
            // backend: this is where the actual export job would be queued
            alert(`Exporting "${this.activeReport}" as ${kind.toUpperCase()} — preview only, no file is generated.`);
        },

        /* ---------------------------------------------------------------
           Daily Sales
           --------------------------------------------------------------- */
        get maxHourly() {
            return Math.max(...this.dailySales.hourly.map((h) => h.sales));
        },

        /* ---------------------------------------------------------------
           Menu Profitability — quadrant classification via median split
           --------------------------------------------------------------- */
        get menuRows() {
            return this.menuProfitability.map((r) => ({
                ...r,
                margin: r.price - r.foodCost,
                foodCostPct: Math.round((r.foodCost / r.price) * 100),
                totalContribution: (r.price - r.foodCost) * r.qtySold,
            }));
        },
        get quadrantMedians() {
            const rows = this.menuRows;
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

        /* ---------------------------------------------------------------
           Generic fallback — every non-flagship report still renders a real,
           internally-consistent (if representative) table + KPIs.
           --------------------------------------------------------------- */
        get genericRows() {
            const rnd = seedFrom(this.activeReport);
            const labels = ['Mon 17', 'Tue 18', 'Wed 19', 'Thu 20', 'Fri 21', 'Sat 22', 'Sun 23'];
            return labels.map((label) => {
                const qty = Math.round(20 + rnd() * 80);
                const amount = Math.round((400 + rnd() * 3200) * (1 + qty / 100));
                return { label, qty, amount };
            });
        },
        get genericTotals() {
            const rows = this.genericRows;
            const total = rows.reduce((s, r) => s + r.amount, 0);
            const qty = rows.reduce((s, r) => s + r.qty, 0);
            return { total, qty, avg: Math.round(total / rows.length), best: rows.reduce((a, b) => (b.amount > a.amount ? b : a)) };
        },
    };
}
