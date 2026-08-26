import Alpine from 'alpinejs';

const inr = (n) => '\u20b9' + Number(n || 0).toLocaleString('en-IN', { maximumFractionDigits: 0 });
const todayIso = () => new Date().toISOString().slice(0, 10);

const data = {
    branches: ['Ichapur Main Branch', 'Salt Lake Express', 'Howrah Cloud Kitchen'],
    ranges: ['Today', 'Week', 'Month', 'Custom'],
    trends: {
        Today: [6200, 9800, 12400, 15800, 19300, 23800, 28900, 33100, 38600, 42800, 47600, 52340],
        Week: [42800, 51200, 48600, 57100, 62500, 68100, 74400],
        Month: [142000, 168000, 151000, 183000, 202000, 196000, 224000, 241000, 232000, 256000, 281000, 306000],
        Custom: [18500, 27400, 32200, 29800, 38700, 44100, 49300],
    },
    kpis: [
        ['Total Sales', 52340, '+12.4%', 'good'],
        ['Orders', 186, '+18', 'good'],
        ['Customers', 142, '+9 new', 'good'],
        ['Avg Order Value', 281, '+\u20b922', 'good'],
        ['Expenses', 18320, '-4.1%', 'warn'],
        ['Profit', 34020, '+16.8%', 'good'],
    ],
    orderStatus: [
        ['New', 8, 'bg-sky-500'],
        ['Preparing', 14, 'bg-orange-500'],
        ['Ready', 6, 'bg-emerald-500'],
        ['Served', 78, 'bg-slate-500'],
        ['Completed', 73, 'bg-violet-500'],
        ['Cancelled', 7, 'bg-rose-500'],
    ],
    channels: [['Dine-In', 104, 58], ['Takeaway', 47, 25], ['Delivery', 35, 17]],
    payments: [['Cash', 18400], ['Card', 12680], ['UPI', 17620], ['Online', 3640]],
    topItems: [
        ['Chicken Biryani', 42, 13440],
        ['Butter Naan', 88, 4400],
        ['Paneer Tikka', 31, 8680],
        ['Chicken Burger', 24, 5280],
        ['Cold Coffee', 29, 4060],
    ],
    inventory: [
        ['Basmati Rice', 'low', '6.5 kg', '12 kg'],
        ['Chicken Breast', 'low', '4.2 kg', '10 kg'],
        ['Ice Cream Cups', 'out', '0 pcs', '30 pcs'],
        ['Coke 300 ml', 'low', '18 btls', '40 btls'],
    ],
    reservations: [['19:00', 'T19 + T24', 'Amit Roy', 6], ['19:30', 'T06', 'Sukanya Sen', 2], ['20:15', 'V02', 'Rahul Agarwal', 8]],
    tables: { total: 28, occupied: 9, reserved: 4, cleaning: 2, available: 13 },
    employees: [
        ['Rahul Das', 38, 14820, 740],
        ['Rakesh Singh', 31, 12150, 608],
        ['Priya Sen', 27, 10430, 522],
        ['Ankit Roy', 22, 8620, 431],
    ],
    kitchen: { avgPrep: 14, delayed: 5, oldest: 47, ready: 6 },
    recent: [
        ['16:39', 'ORD-2026-0041 paid by UPI', 'success'],
        ['16:31', 'T06 moved to billing', 'info'],
        ['16:22', 'Chicken Breast stock below reorder level', 'warn'],
        ['16:11', 'Reservation RES-318 marked arrived', 'info'],
        ['16:04', 'KOT #1082 delayed at Tandoor', 'danger'],
    ],
    alerts: [
        ['KOT Delay', '5 orders crossed target prep time', 'danger'],
        ['Inventory', 'Ice Cream Cups out of stock', 'danger'],
        ['Cash Drawer', '\u20b9500 variance pending approval', 'warn'],
    ],
};

function dashboardApp() {
    return {
        branch: data.branches[0],
        range: 'Today',
        dateFrom: todayIso(),
        dateTo: todayIso(),
        loading: false,
        error: '',
        data,
        money: inr,
        applyFilters() {
            this.error = '';
            this.loading = true;
            setTimeout(() => (this.loading = false), 250);
        },
        setRange(range) {
            this.loading = true;
            this.range = range;
            setTimeout(() => (this.loading = false), 350);
        },
        trendPoints() {
            const values = this.data.trends[this.range] || [];
            const max = Math.max(...values, 1);
            const min = Math.min(...values, 0);
            return values.map((value, index) => {
                const x = 12 + (index * 276) / Math.max(values.length - 1, 1);
                const y = 112 - ((value - min) * 88) / Math.max(max - min, 1);
                return `${x},${y}`;
            }).join(' ');
        },
        totalPayments() {
            return this.data.payments.reduce((sum, row) => sum + row[1], 0);
        },
        paymentWidth(amount) {
            return `${Math.max(4, (amount / Math.max(this.totalPayments(), 1)) * 100)}%`;
        },
        tablePercent(key) {
            return Math.round((this.data.tables[key] / this.data.tables.total) * 100);
        },
        statusTone(status) {
            return {
                low: 'border-amber-300 bg-amber-50 text-amber-800',
                out: 'border-rose-300 bg-rose-50 text-rose-700',
                success: 'border-emerald-300 bg-emerald-50 text-emerald-800',
                info: 'border-sky-300 bg-sky-50 text-sky-800',
                warn: 'border-amber-300 bg-amber-50 text-amber-800',
                danger: 'border-rose-300 bg-rose-50 text-rose-700',
            }[status] || 'border-slate-300 bg-slate-50 text-slate-700';
        },
    };
}

window.Alpine = Alpine;
Alpine.data('dashboardApp', dashboardApp);
Alpine.start();
