/**
 * Billing, Payment & Split Bill — demo data.
 *
 * Presentation-layer fixtures only, same spirit as the POS/Floor/KDS demo
 * data files. The bill is deliberately padded past 20 lines (per the brief's
 * "20+ items" scroll test) by extending the realistic headline items from
 * the spec with additional ordinary lines — the featured items keep the
 * spec's exact rate/amount figures so the math stays traceable.
 */

export const VENUE = {
    name: 'Royal Bengal Restaurant',
    branch: 'Ichapur Main Branch',
    gstin: '19AABCR1234K1Z8',
    address: '18 Grand Trunk Road, Ichapur, North 24 Parganas, WB 743144',
    phone: '+91 33 2593 4400',
};

export const OPERATOR = {
    name: 'Amit',
    role: 'Cashier',
    initials: 'A',
    shift: 'OPEN',
    terminal: 'POS-01',
    /** Discounts above this need a manager PIN — mirrors the POS terminal's rule. */
    discountLimitPct: 10,
};

export const INVOICE = {
    code: 'INV-2026-001028',
    createdLabel: 'Today',
};

export const ORDER = {
    code: 'ORD-1028',
    type: 'dinein', // dinein | takeaway | delivery
    table: 'T08',
    floor: 'Ground Floor',
    guests: 4,
    waiter: 'Rahul',
    startedMinutesAgo: 42,
};

export const CUSTOMER = {
    name: 'Nirmal Chakraborty',
    phone: '9830112244',
    email: '',
    gstin: '',
    businessName: '',
    address: '',
    loyalty: { points: 185, valuePerPoint: 1 },
};

export const CHARGES = {
    taxMode: 'exclusive', // exclusive | inclusive
    cgstRate: 0.025,
    sgstRate: 0.025,
    serviceRate: 0.05,
    serviceLocked: true,
    serviceLabel: 'Service Charge',
};

/**
 * status: normal | complimentary | discounted | cancelled | refunded
 * Only the first 7 lines carry the spec's literal rate/amount figures;
 * everything after "Ice Cream" is scroll-test padding.
 */
let uid = 1;
const line = (name, qty, rate, extra = {}) => ({
    uid: uid++,
    name,
    qty,
    rate,
    amount: qty * rate,
    status: 'normal',
    discount: 0,
    discountReason: null,
    compReason: null,
    compBy: null,
    cancelReason: null,
    refundReason: null,
    refundAmount: 0,
    note: null,
    kot: 1024,
    orderedAt: '8:10 PM',
    ...extra,
});

export const ITEMS = [
    line('Chicken Biryani', 2, 320, { note: 'Less Spicy' }),
    line('Mutton Biryani', 1, 420, { note: 'Extra Gravy' }),
    line('Paneer Tikka', 1, 280, { status: 'complimentary', compReason: 'Owner Courtesy', compBy: 'Priya (Manager)' }),
    line('Butter Naan', 4, 50),
    line('Chicken Tikka', 1, 360, { status: 'discounted', discount: 60, discountReason: 'Regular guest' }),
    line('Coke', 2, 60),
    line('Ice Cream', 2, 120),
    line('Mutton Rogan Josh', 1, 450, { status: 'cancelled', cancelReason: 'Guest changed mind', kot: 1045 }),
    line('Fresh Lime Soda', 1, 90, { status: 'refunded', refundReason: 'Wrong item served', refundAmount: 90, kot: 1024 }),

    // — scroll-test padding (ordinary lines, second KOT round) —
    line('Garlic Naan', 3, 70, { kot: 1046, orderedAt: '8:24 PM' }),
    line('Dal Makhani', 1, 240, { kot: 1046, orderedAt: '8:24 PM' }),
    line('Veg Fried Rice', 1, 210, { kot: 1046, orderedAt: '8:24 PM' }),
    line('Chilli Chicken', 1, 290, { kot: 1046, orderedAt: '8:24 PM' }),
    line('Tandoori Roti', 4, 30, { kot: 1046, orderedAt: '8:24 PM' }),
    line('Masala Papad', 2, 60, { kot: 1046, orderedAt: '8:24 PM' }),
    line('Gulab Jamun (2 pcs)', 2, 90, { kot: 1046, orderedAt: '8:24 PM' }),
    line('Masala Coffee', 2, 80, { kot: 1046, orderedAt: '8:24 PM' }),
    line('Paneer Butter Masala', 1, 300, { kot: 1051, orderedAt: '8:38 PM' }),
    line('Jeera Rice', 1, 160, { kot: 1051, orderedAt: '8:38 PM' }),
    line('Cheese Chilli Naan', 2, 110, { kot: 1051, orderedAt: '8:38 PM' }),
    line('Sweet Lassi', 2, 110, { kot: 1051, orderedAt: '8:38 PM' }),
    line('Mineral Water (1 L)', 2, 20, { kot: 1051, orderedAt: '8:38 PM' }),
    line('Rasmalai', 2, 140, { kot: 1051, orderedAt: '8:38 PM' }),
];

/** Seeded already-applied bill-level discount, so the "approved" state is visible on load. */
export const BILL_DISCOUNT = {
    mode: 'pct',
    value: 12,
    reason: 'Customer loyalty',
    approvedBy: 'Priya (Manager)',
};

/** One partial cash tender seeded so the screen opens mid-settlement, per the brief. */
export const PAYMENTS = [
    { method: 'cash', label: 'Cash', amount: 500, reference: null, at: '8:52 PM', status: 'success' },
];

export const PAYMENT_METHODS = [
    { key: 'cash', label: 'Cash' },
    { key: 'upi', label: 'UPI' },
    { key: 'credit', label: 'Credit Card' },
    { key: 'debit', label: 'Debit Card' },
    { key: 'wallet', label: 'Wallet' },
    { key: 'bank', label: 'Bank Transfer' },
    { key: 'other', label: 'Other' },
];

export const DISCOUNT_REASONS = ['Customer loyalty', 'Service delay', 'Manager courtesy', 'Promotional offer', 'Corporate tie-up'];
export const COMP_REASONS = ['Customer Complaint', 'Owner Courtesy', 'Promotion', 'Staff Meal', 'Other'];
export const CANCEL_REASONS = ['Guest changed mind', 'Kitchen error', 'Duplicate entry', 'Other'];
export const REFUND_REASONS = ['Customer complaint', 'Wrong item served', 'Quality issue', 'Billing error', 'Other'];
export const VOID_REASONS = ['Duplicate bill', 'Wrong table', 'Testing / training', 'Other'];
export const VALID_COUPONS = { WELCOME10: 10, FESTIVE15: 15 };

export const WAITERS = ['Rahul', 'Imran', 'Nabila', 'Sujoy', 'Deepa'];

export const CUSTOMERS = [
    { id: 'C1', name: 'Nirmal Chakraborty', phone: '9830112244', visits: 12, spend: 24600, points: 185 },
    { id: 'C2', name: 'Sourav Banerjee', phone: '9830112245', visits: 27, spend: 48600, points: 1240 },
    { id: 'C3', name: 'Ananya Dutta', phone: '9007556621', visits: 9, spend: 12400, points: 310 },
    { id: 'C4', name: 'Imtiaz Rahman', phone: '9836774410', visits: 41, spend: 96200, points: 2680 },
];

export const SHORTCUTS = [
    { keys: 'F2', label: 'Customer' },
    { keys: 'F4', label: 'Apply discount' },
    { keys: 'F6', label: 'Split bill' },
    { keys: 'F8', label: 'Focus payment amount' },
    { keys: 'F9', label: 'Complete payment' },
    { keys: 'Ctrl+P', label: 'Print' },
    { keys: 'Esc', label: 'Close modal' },
];
