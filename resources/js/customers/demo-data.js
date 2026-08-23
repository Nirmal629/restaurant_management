export const VENUE = { name: 'Royal Bengal Restaurant', branch: 'Ichapur Main Branch' };
export const OPERATOR = { name: 'Amit Sharma', role: 'Cashier' };

export const TAGS = ['Regular', 'Foodie', 'Corporate', 'Family', 'Loyal'];

const c = (o) => ({
    email: '', birthday: '', anniversary: '', address: '', gstin: '', notes: '', tags: [], allergies: '',
    vip: false, favoriteItems: [], recentOrders: [], reservationsCount: 0, joinedDate: '2024-01-15',
    ...o,
});

export const CUSTOMERS = [
    c({ id: 'C1', name: 'Nirmal Chakraborty', phone: '9830112244', email: 'nirmal.c@example.com', visits: 18, spend: 42850, avgBill: 2380, lastVisit: '2026-08-23', points: 420, tags: ['Regular', 'Foodie'], vip: true, birthday: '1986-08-23', favoriteItems: ['Chicken Biryani', 'Chicken Tikka', 'Coke'], recentOrders: [{ code: 'ORD-1028', amount: 2280, date: '2026-08-23' }, { code: 'ORD-0982', amount: 1850, date: '2026-08-10' }, { code: 'ORD-0911', amount: 2640, date: '2026-07-28' }], reservationsCount: 3, notes: 'Prefers window-side table. Regular Friday visitor.' }),
    c({ id: 'C2', name: 'Sourav Banerjee', phone: '9830112245', email: 'sourav.b@example.com', visits: 27, spend: 48600, avgBill: 1800, lastVisit: '2026-08-20', points: 1240, tags: ['Regular', 'Corporate'], vip: true, recentOrders: [{ code: 'ORD-1019', amount: 2400, date: '2026-08-20' }], reservationsCount: 5 }),
    c({ id: 'C3', name: 'Ananya Dutta', phone: '9007556621', email: 'ananya.d@example.com', visits: 9, spend: 12400, avgBill: 1377, lastVisit: '2026-07-30', points: 310, tags: ['Foodie'], anniversary: '2020-11-02', recentOrders: [{ code: 'ORD-0870', amount: 1450, date: '2026-07-30' }], reservationsCount: 1 }),
    c({ id: 'C4', name: 'Imtiaz Rahman', phone: '9836774410', email: 'imtiaz.r@example.com', visits: 41, spend: 96200, avgBill: 2346, lastVisit: '2026-08-24', points: 2680, tags: ['Regular', 'Loyal', 'Corporate'], vip: true, gstin: '19AAAAA0000A1Z5', reservationsCount: 8 }),
    c({ id: 'C5', name: 'Priya Ghosh', phone: '9163302299', email: '', visits: 4, spend: 5200, avgBill: 1300, lastVisit: '2026-05-12', points: 120, tags: [] }),
    c({ id: 'C6', name: 'Rohit Sharma', phone: '9748110034', email: 'rohit.sharma@example.com', visits: 15, spend: 27800, avgBill: 1853, lastVisit: '2026-08-18', points: 690, tags: ['Family'], anniversary: '2026-08-30' }),
    c({ id: 'C7', name: 'Farhan Ali', phone: '9903448821', email: '', visits: 2, spend: 1900, avgBill: 950, lastVisit: '2026-04-02', points: 40 }),
    c({ id: 'C8', name: 'Amit Roy', phone: '9830112245', email: 'amit.roy@example.com', visits: 6, spend: 9800, avgBill: 1633, lastVisit: '2026-08-23', points: 190, tags: ['Family'], birthday: '2026-08-28' }),
    c({ id: 'C9', name: 'Priya Das', phone: '9007556621', email: '', visits: 3, spend: 4100, avgBill: 1366, lastVisit: '2026-08-23', points: 82 }),
    c({ id: 'C10', name: 'Arjun Sen', phone: '9748899001', email: 'arjun.sen@example.com', visits: 22, spend: 55400, avgBill: 2518, lastVisit: '2026-08-23', points: 980, tags: ['Corporate', 'Regular'], vip: true },),
    c({ id: 'C11', name: 'Rahul Sen', phone: '9836774410', email: '', visits: 1, spend: 780, avgBill: 780, lastVisit: '2026-08-23', points: 15 }),
    c({ id: 'C12', name: 'S. Sen', phone: '9830011223', email: '', visits: 5, spend: 6300, avgBill: 1260, lastVisit: '2026-03-11', points: 60 }),
];

export const DISCOUNT_LOG = {
    C1: [
        { at: '23/08/2026', text: 'Loyalty: 100 pts redeemed for ₹100 on ORD-1028' },
        { at: '10/08/2026', text: 'Loyalty: +91 pts earned on ORD-0982' },
    ],
};
