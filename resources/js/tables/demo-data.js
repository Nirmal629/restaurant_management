/**
 * Floor & Table Management — demo data.
 *
 * Presentation-layer fixtures only, in the same spirit as resources/js/pos/demo-data.js.
 * Every table lives on exactly one floor; `groupId` links tables that have been
 * merged into a single running session (see `store.js#cardGroups`).
 */

export const VENUE = {
    name: 'Royal Bengal Restaurant',
    branch: 'Ichapur Main Branch',
};

export const OPERATOR = {
    name: 'Rahul',
    role: 'Captain',
    initials: 'R',
    shift: 'OPEN',
};

export const FLOORS = [
    { key: 'ground', label: 'Ground Floor' },
    { key: 'first', label: 'First Floor' },
    { key: 'outdoor', label: 'Outdoor' },
    { key: 'vip', label: 'VIP Section' },
];

export const WAITERS = ['Rahul', 'Ankit', 'Suman', 'Priya'];

/** Thresholds are configuration, not hardcoded business rules baked into the UI. */
export const CONFIG = {
    longRunningMinutes: 30,
    cleaningWarnMinutes: 12,
};

/**
 * status: available | occupied | reserved | billing | cleaning | disabled
 * shape:  square | round | rect          (rect cards span 2 grid columns)
 * groupId links tables merged into one session; groupPrimary marks the table
 * whose order/waiter/customer the group card displays.
 */
export const TABLES = [
    // ---------------------------------------------------------------- Ground
    { id: 'T01', floor: 'ground', seats: 4, shape: 'square', status: 'available' },
    {
        id: 'T02', floor: 'ground', seats: 4, shape: 'square', status: 'occupied',
        guests: 4, waiter: 'Rahul', customer: 'Nirmal Chakraborty', orderCode: 'ORD-1028',
        amount: 1850, since: 18, kitchen: { new: 1, prep: 2, ready: 1 },
        items: [
            { name: 'Chicken Biryani', qty: 2, state: 'PREPARING' },
            { name: 'Paneer Tikka', qty: 1, state: 'READY' },
            { name: 'Coke', qty: 2, state: 'SERVED' },
        ],
    },
    {
        id: 'T03', floor: 'ground', seats: 6, shape: 'rect', status: 'reserved',
        reservationId: 'RES-204',
    },
    {
        id: 'T04', floor: 'ground', seats: 2, shape: 'round', status: 'billing',
        guests: 2, orderCode: 'ORD-1019', amount: 2400, since: 62, waiter: 'Rahul',
    },
    { id: 'T05', floor: 'ground', seats: 4, shape: 'square', status: 'cleaning', since: 8 },
    { id: 'T06', floor: 'ground', seats: 2, shape: 'round', status: 'disabled', note: 'Leg under repair' },
    { id: 'T07', floor: 'ground', seats: 6, shape: 'rect', status: 'available' },
    {
        id: 'T08', floor: 'ground', seats: 6, shape: 'rect', status: 'occupied',
        guests: 8, waiter: 'Ankit', customer: 'Ghosh Party', orderCode: 'ORD-1031',
        amount: 3850, since: 32, kitchen: { new: 0, prep: 1, ready: 1 },
        groupId: 'G1', groupPrimary: true,
        items: [
            { name: 'Mutton Biryani', qty: 3, state: 'PREPARING' },
            { name: 'Butter Naan', qty: 6, state: 'READY' },
            { name: 'Chilli Chicken', qty: 2, state: 'SERVED' },
        ],
    },
    { id: 'T09', floor: 'ground', seats: 4, shape: 'square', status: 'occupied', groupId: 'G1', groupPrimary: false },
    { id: 'T10', floor: 'ground', seats: 8, shape: 'rect', status: 'available' },

    // ------------------------------------------------------------ First Floor
    { id: 'T11', floor: 'first', seats: 4, shape: 'square', status: 'available' },
    { id: 'T12', floor: 'first', seats: 4, shape: 'square', status: 'reserved', reservationId: 'RES-205' },
    {
        id: 'T13', floor: 'first', seats: 6, shape: 'rect', status: 'occupied',
        guests: 5, waiter: 'Suman', customer: 'Deb Family', orderCode: 'ORD-1033',
        amount: 2750, since: 22, kitchen: { new: 1, prep: 1, ready: 0 },
        items: [
            { name: 'Fish Kalia', qty: 1, state: 'PREPARING' },
            { name: 'Steamed Rice', qty: 3, state: 'SENT' },
        ],
    },
    { id: 'T14', floor: 'first', seats: 2, shape: 'round', status: 'available' },
    {
        id: 'T15', floor: 'first', seats: 8, shape: 'rect', status: 'occupied',
        guests: 7, waiter: 'Priya', customer: 'Sen Anniversary', orderCode: 'ORD-1029',
        amount: 4200, since: 41, kitchen: { new: 0, prep: 2, ready: 0 },
        items: [
            { name: 'Tandoori Chicken', qty: 2, state: 'PREPARING' },
            { name: 'Veg Biryani', qty: 2, state: 'PREPARING' },
        ],
    },
    { id: 'T16', floor: 'first', seats: 4, shape: 'square', status: 'available' },
    { id: 'T17', floor: 'first', seats: 6, shape: 'rect', status: 'reserved', reservationId: 'RES-206' },
    { id: 'T18', floor: 'first', seats: 4, shape: 'square', status: 'available' },

    // ---------------------------------------------------------------- Outdoor
    { id: 'T19', floor: 'outdoor', seats: 4, shape: 'square', status: 'available' },
    {
        id: 'T20', floor: 'outdoor', seats: 6, shape: 'rect', status: 'occupied',
        guests: 4, waiter: 'Rahul', customer: 'Walk-in', orderCode: 'ORD-1035',
        amount: 1400, since: 14, kitchen: { new: 2, prep: 0, ready: 0 },
        items: [{ name: 'Veg Hakka Noodles', qty: 2, state: 'SENT' }, { name: 'Fresh Lime Soda', qty: 2, state: 'SENT' }],
    },
    { id: 'T21', floor: 'outdoor', seats: 4, shape: 'square', status: 'available' },
    {
        id: 'T22', floor: 'outdoor', seats: 4, shape: 'square', status: 'occupied',
        guests: 2, waiter: 'Priya', customer: 'Walk-in', orderCode: 'ORD-1036',
        amount: 640, since: 9, kitchen: { new: 1, prep: 0, ready: 0 },
        items: [{ name: 'Cold Coffee', qty: 2, state: 'SENT' }],
    },
    { id: 'T23', floor: 'outdoor', seats: 6, shape: 'rect', status: 'reserved', reservationId: 'RES-207' },
    { id: 'T24', floor: 'outdoor', seats: 2, shape: 'round', status: 'cleaning', since: 4 },

    // ------------------------------------------------------------ VIP Section
    {
        id: 'V01', floor: 'vip', seats: 6, shape: 'rect', status: 'occupied',
        guests: 5, waiter: 'Ankit', customer: 'Roy Chowdhury', orderCode: 'ORD-1027',
        amount: 5200, since: 55, kitchen: { new: 0, prep: 2, ready: 0 },
        items: [{ name: 'Tandoori Pomfret', qty: 1, state: 'PREPARING' }, { name: 'Mutton Rogan Josh', qty: 2, state: 'PREPARING' }],
    },
    { id: 'V02', floor: 'vip', seats: 8, shape: 'rect', status: 'reserved', reservationId: 'RES-208' },
    { id: 'V03', floor: 'vip', seats: 6, shape: 'rect', status: 'available' },
    { id: 'V04', floor: 'vip', seats: 4, shape: 'square', status: 'disabled', note: 'Under renovation' },
];

export const RESERVATIONS = [
    { id: 'RES-204', tableId: 'T03', customer: 'Amit Roy', phone: '9876543210', date: 'Today', time: '7:30 PM', guests: 6, notes: 'Birthday dinner', status: 'CONFIRMED' },
    { id: 'RES-205', tableId: 'T12', customer: 'S. Sen', phone: '9830011223', date: 'Today', time: '7:45 PM', guests: 4, notes: '', status: 'CONFIRMED' },
    { id: 'RES-206', tableId: 'T17', customer: 'K. Iyer', phone: '9748899001', date: 'Today', time: '8:15 PM', guests: 5, notes: 'Prefers window side', status: 'CONFIRMED' },
    { id: 'RES-207', tableId: 'T23', customer: 'F. Ali', phone: '9903448821', date: 'Today', time: '8:00 PM', guests: 4, notes: '', status: 'CONFIRMED' },
    { id: 'RES-208', tableId: 'V02', customer: 'Sarkar Family', phone: '9007556621', date: 'Today', time: '9:00 PM', guests: 8, notes: 'Anniversary — cake arranged', status: 'CONFIRMED' },
];
