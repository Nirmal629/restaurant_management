export const VENUE = { name: 'Royal Bengal Restaurant', branch: 'Ichapur Main Branch' };

/** Category → report list, exactly as specified. Some keys (e.g. Sales by Waiter)
 *  intentionally appear under two categories — they point at the same report. */
export const CATEGORIES = [
    { key: 'sales', label: 'Sales', reports: ['Daily Sales', 'Sales by Date', 'Sales by Hour', 'Sales by Order Type', 'Sales by Payment Method', 'Sales by Waiter', 'Sales by Table', 'Discount Report', 'Cancellation/Void Report', 'Tax Report'] },
    { key: 'menu', label: 'Menu', reports: ['Item Sales', 'Category Sales', 'Top Selling Items', 'Least Selling Items', 'Menu Profitability', 'Food Cost', 'Contribution Margin'] },
    { key: 'kitchen', label: 'Kitchen', reports: ['Average Preparation Time', 'Delayed Orders', 'Station Performance', 'Item Preparation Time'] },
    { key: 'inventory', label: 'Inventory', reports: ['Current Stock', 'Stock Movement', 'Consumption', 'Wastage', 'Low Stock', 'Inventory Valuation', 'Stock Variance'] },
    { key: 'purchase', label: 'Purchase', reports: ['Purchase Summary', 'Supplier Purchases', 'Ingredient Purchase History'] },
    { key: 'customer', label: 'Customer', reports: ['New Customers', 'Returning Customers', 'Top Customers', 'Customer Spend', 'Loyalty Usage'] },
    { key: 'financial', label: 'Financial', reports: ['Revenue', 'Expenses', 'Gross Profit Estimate', 'Payment Summary', 'Cash vs Digital', 'Discounts', 'Refunds'] },
    { key: 'employee', label: 'Employee', reports: ['Sales by Waiter', 'Orders by Waiter', 'Discounts by Employee', 'Voids/Cancellations by Employee'] },
];

/** Flagship reports get bespoke, fully-worked datasets. */
export const DAILY_SALES = {
    kpis: { grossSales: 182450, discount: 8250, netSales: 174200, orders: 286, avgBill: 609, guests: 518 },
    hourly: [
        { hour: '12 PM', sales: 8200 }, { hour: '1 PM', sales: 14500 }, { hour: '2 PM', sales: 19800 },
        { hour: '3 PM', sales: 9200 }, { hour: '4 PM', sales: 6100 }, { hour: '5 PM', sales: 8700 },
        { hour: '6 PM', sales: 15300 }, { hour: '7 PM', sales: 28900 }, { hour: '8 PM', sales: 34200 },
        { hour: '9 PM', sales: 26800 }, { hour: '10 PM', sales: 10650 },
    ],
    transactions: [
        { code: 'ORD-1028', time: '19:12', type: 'Dine In', waiter: 'Rahul Das', amount: 2280, payment: 'Mixed' },
        { code: 'ORD-1027', time: '19:05', type: 'Dine In', waiter: 'Ankit Roy', amount: 5200, payment: 'Card' },
        { code: 'ORD-1026', time: '18:50', type: 'Takeaway', waiter: '—', amount: 680, payment: 'UPI' },
        { code: 'ORD-1025', time: '18:40', type: 'Dine In', waiter: 'Priya Sen', amount: 1450, payment: 'Cash' },
        { code: 'ORD-1024', time: '18:22', type: 'Delivery', waiter: '—', amount: 940, payment: 'UPI' },
        { code: 'ORD-1023', time: '17:58', type: 'Dine In', waiter: 'Suman Ghosh', amount: 2100, payment: 'Card' },
        { code: 'ORD-1022', time: '17:30', type: 'Dine In', waiter: 'Rahul Das', amount: 1780, payment: 'Cash' },
        { code: 'ORD-1021', time: '16:45', type: 'Takeaway', waiter: '—', amount: 420, payment: 'UPI' },
    ],
};

export const MENU_PROFITABILITY = [
    { item: 'Chicken Biryani', price: 320, foodCost: 96, qtySold: 412, category: 'Biryani' },
    { item: 'Mutton Biryani', price: 420, foodCost: 168, qtySold: 118, category: 'Biryani' },
    { item: 'Paneer Tikka', price: 280, foodCost: 70, qtySold: 265, category: 'Starters' },
    { item: 'Chicken Tikka', price: 360, foodCost: 126, qtySold: 190, category: 'Starters' },
    { item: 'Butter Chicken', price: 380, foodCost: 133, qtySold: 96, category: 'Indian' },
    { item: 'Veg Fried Rice', price: 220, foodCost: 55, qtySold: 74, category: 'Chinese' },
    { item: 'Chicken Fried Rice', price: 280, foodCost: 84, qtySold: 61, category: 'Chinese' },
    { item: 'Chilli Chicken', price: 340, foodCost: 119, qtySold: 38, category: 'Chinese' },
    { item: 'Butter Naan', price: 50, foodCost: 12, qtySold: 588, category: 'Bread' },
    { item: 'Coke', price: 60, foodCost: 22, qtySold: 340, category: 'Beverages' },
    { item: 'Ice Cream', price: 120, foodCost: 40, qtySold: 21, category: 'Desserts' },
    { item: 'Dal Makhani', price: 240, foodCost: 62, qtySold: 29, category: 'Indian' },
];

export const REVENUE_SUMMARY = {
    revenue: 1782450, cogs: 534735, expenses: 218400, grossProfit: 1029315,
    months: [
        { m: 'Mar', revenue: 1420000 }, { m: 'Apr', revenue: 1510000 }, { m: 'May', revenue: 1465000 },
        { m: 'Jun', revenue: 1602000 }, { m: 'Jul', revenue: 1690000 }, { m: 'Aug', revenue: 1782450 },
    ],
};

export const WAITER_SALES = [
    { name: 'Rahul Das', orders: 68, sales: 41200, avgBill: 606, tables: 24, discounts: 1200, voids: 1 },
    { name: 'Ankit Roy', orders: 54, sales: 38900, avgBill: 720, tables: 19, discounts: 800, voids: 0 },
    { name: 'Suman Ghosh', orders: 61, sales: 33450, avgBill: 548, tables: 22, discounts: 600, voids: 2 },
    { name: 'Priya Sen', orders: 47, sales: 29800, avgBill: 634, tables: 17, discounts: 400, voids: 0 },
];

export const CURRENCY_KEYS_FOR_FORMAT_HINT = true;
