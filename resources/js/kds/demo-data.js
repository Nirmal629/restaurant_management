/**
 * Kitchen Display System — demo data.
 *
 * Presentation-layer fixtures only, same spirit as the POS and Floor/Table
 * demo data files. Minutes-ago values are converted to real timestamps by
 * the store at init() so wait timers tick live in the browser.
 *
 * Ticket model: NEW → ACCEPTED → PREPARING are whole-ticket transitions (one
 * action moves every item at once, matching the single ACCEPT / START
 * PREPARING buttons in the brief). Only within PREPARING do items resolve
 * individually — that is what produces the "partially ready" state — and the
 * ticket auto-promotes to READY once every non-cancelled item is ready.
 */

export const VENUE = {
    name: 'Royal Bengal Restaurant',
    branch: 'Ichapur Main Branch',
};

export const OPERATOR = {
    name: 'Rahul',
    role: 'Chef',
    initials: 'CR',
    shift: 'OPEN',
};

export const STATIONS = [
    { key: 'all', label: 'All Kitchens' },
    { key: 'main', label: 'Main Kitchen' },
    { key: 'tandoor', label: 'Tandoor' },
    { key: 'chinese', label: 'Chinese' },
    { key: 'beverage', label: 'Beverage' },
    { key: 'dessert', label: 'Dessert' },
    { key: 'bar', label: 'Bar' },
];

/** Thresholds are configuration, never hardcoded into the wait-time UI. */
export const CONFIG = {
    warnMinutes: 15,
    criticalMinutes: 25,
};

export const CANCEL_REASONS = ['Printer issue', 'Kitchen copy', 'Other'];
export const UNAVAILABLE_REASONS = ['Ingredient unavailable', 'Sold out', 'Equipment issue', 'Other'];

let seq = 1;
const item = (name, qty, station, extra = {}) => ({
    uid: seq++,
    name,
    qty,
    station,
    variant: null,
    modifiers: [],
    note: null,
    allergy: null,
    course: null,
    fire: null,
    status: 'pending', // pending | ready | unavailable | cancel_requested | cancelled
    ...extra,
});

/**
 * status: new | accepted | preparing | ready | picked_up
 * priority: normal | priority | rush | vip | waiting
 * orderType: dinein | takeaway | delivery
 */
export const TICKETS = [
    // ---------------------------------------------------------------- NEW
    {
        kot: 1048, round: 1, orderCode: 'ORD-1041', orderType: 'dinein', table: 'T03',
        waiter: 'Suman', guests: 2, placedMinutesAgo: 3, status: 'new', priority: 'normal',
        items: [
            item('Mutton Biryani', 1, 'main', { note: 'Medium Spicy' }),
            item('Chicken Fried Rice', 1, 'chinese'),
        ],
    },
    {
        kot: 1049, round: 1, orderCode: 'ORD-1042', orderType: 'takeaway', table: null, token: 'T106',
        waiter: null, guests: null, placedMinutesAgo: 2, status: 'new', priority: 'normal',
        items: [item('Chicken Biryani', 2, 'main', { note: 'No Onion' })],
    },
    {
        kot: 1050, round: 1, orderCode: 'ORD-1043', orderType: 'dinein', table: 'T14',
        waiter: 'Ankit', guests: 2, placedMinutesAgo: 1, status: 'new', priority: 'normal',
        items: [item('Veg Hakka Noodles', 1, 'chinese'), item('Fresh Lime Soda', 1, 'beverage')],
    },
    {
        kot: 1052, round: 1, orderCode: 'ORD-1044', orderType: 'delivery', table: null, token: 'D503',
        waiter: null, guests: null, placedMinutesAgo: 4, status: 'new', priority: 'normal',
        items: [item('Paneer Butter Masala', 1, 'main'), item('Butter Naan', 3, 'tandoor')],
    },
    {
        kot: 1053, round: 1, orderCode: 'ORD-1045', orderType: 'dinein', table: 'T18',
        waiter: 'Priya', guests: 4, placedMinutesAgo: 1, status: 'new', priority: 'priority',
        items: [
            item('Tandoori Pomfret', 1, 'tandoor', { allergy: 'ALLERGY: SHELLFISH TRACE' }),
            item('Dal Makhani', 1, 'main'),
        ],
    },
    {
        kot: 1054, round: 1, orderCode: 'ORD-1046', orderType: 'dinein', table: 'T02',
        waiter: 'Rahul', guests: 4, placedMinutesAgo: 6, status: 'new', priority: 'waiting',
        items: [item('Coke', 2, 'beverage'), item('Masala Papad', 2, 'starter')],
    },
    {
        kot: 1055, round: 1, orderCode: 'ORD-1047', orderType: 'takeaway', table: null, token: 'T107',
        waiter: null, guests: null, placedMinutesAgo: 5, status: 'new', priority: 'normal',
        items: [item('Chicken Tikka Masala', 1, 'main'), item('Steamed Rice', 1, 'main')],
    },
    {
        kot: 1056, round: 1, orderCode: 'ORD-1048', orderType: 'dinein', table: 'T21',
        waiter: 'Suman', guests: 3, placedMinutesAgo: 2, status: 'new', priority: 'normal',
        items: [item('Chilli Chicken', 1, 'chinese'), item('Veg Manchurian Gravy', 1, 'chinese'), item('Garlic Naan', 2, 'tandoor')],
    },
    {
        kot: 1057, round: 2, orderCode: 'ORD-1028', orderType: 'dinein', table: 'T08',
        waiter: 'Rahul', guests: 4, placedMinutesAgo: 1, status: 'new', priority: 'normal',
        items: [item('Gulab Jamun (2 pcs)', 2, 'dessert'), item('Masala Coffee', 2, 'beverage')],
    },
    {
        kot: 1058, round: 1, orderCode: 'ORD-1049', orderType: 'dinein', table: 'F02',
        waiter: 'Nabila', guests: 2, placedMinutesAgo: 0, status: 'new', priority: 'normal',
        items: [item('Cold Coffee with Ice Cream', 2, 'beverage')],
    },
    {
        kot: 1059, round: 1, orderCode: 'ORD-1050', orderType: 'delivery', table: null, token: 'D504',
        waiter: null, guests: null, placedMinutesAgo: 7, status: 'new', priority: 'rush',
        items: [item('Chicken Biryani', 3, 'main', { note: 'Extra Raita' })],
    },

    // ---------------------------------------------------------- ACCEPTED
    {
        kot: 1046, round: 1, orderCode: 'ORD-1051', orderType: 'dinein', table: 'T11',
        waiter: 'Priya', guests: 3, placedMinutesAgo: 8, acceptedMinutesAgo: 6, status: 'accepted', priority: 'normal',
        items: [
            item('Chicken Biryani', 2, 'main'),
            item('Veg Fried Rice', 1, 'chinese'),
            item('Papad', 2, 'starter', { status: 'cancelled', note: 'Guest cancelled before firing' }),
        ],
    },
    {
        kot: 1060, round: 1, orderCode: 'ORD-1052', orderType: 'takeaway', table: null, token: 'T108',
        waiter: null, guests: null, placedMinutesAgo: 9, acceptedMinutesAgo: 7, status: 'accepted', priority: 'normal',
        items: [item('Fish Kalia', 1, 'main'), item('Steamed Rice', 2, 'main')],
    },
    {
        kot: 1061, round: 1, orderCode: 'ORD-1053', orderType: 'dinein', table: 'T24',
        waiter: 'Priya', guests: 2, placedMinutesAgo: 6, acceptedMinutesAgo: 5, status: 'accepted', priority: 'normal',
        items: [
            item('Chicken Lollipop', 1, 'chinese'),
            item('Paneer Tikka', 1, 'tandoor', { course: 'starter', fire: 'fire' }),
            item('Mutton Rogan Josh', 1, 'main', { course: 'main', fire: 'hold', note: 'Hold — fire with dessert call' }),
        ],
    },

    // ---------------------------------------------------------- PREPARING
    {
        kot: 1045, round: 1, orderCode: 'ORD-1028', orderType: 'dinein', table: 'T08',
        waiter: 'Rahul', guests: 4, placedMinutesAgo: 20, acceptedMinutesAgo: 19, startedMinutesAgo: 18,
        status: 'preparing', priority: 'normal',
        items: [
            item('Chicken Biryani', 2, 'main', { note: 'Less Spicy', status: 'pending' }),
            item('Mutton Biryani', 1, 'main', { modifiers: ['Extra Gravy'], status: 'pending' }),
        ],
    },
    {
        kot: 1041, round: 1, orderCode: 'ORD-1054', orderType: 'dinein', table: 'T15',
        waiter: 'Priya', guests: 6, placedMinutesAgo: 27, acceptedMinutesAgo: 26, startedMinutesAgo: 24,
        status: 'preparing', priority: 'priority',
        items: [
            item('Chicken Biryani', 3, 'main', { note: 'NO ONION', status: 'pending' }),
            item('Veg Biryani', 1, 'main', { status: 'ready' }),
            item('Mutton Seekh Kebab', 1, 'tandoor', { status: 'unavailable', unavailableReason: 'Ingredient unavailable' }),
        ],
    },
    {
        kot: 1062, round: 1, orderCode: 'ORD-1055', orderType: 'dinein', table: 'T05',
        waiter: 'Ankit', guests: 2, placedMinutesAgo: 12, acceptedMinutesAgo: 11, startedMinutesAgo: 10,
        status: 'preparing', priority: 'normal',
        items: [
            item('Chicken Tikka', 1, 'tandoor', { status: 'ready' }),
            item('Butter Naan', 2, 'tandoor', { status: 'pending' }),
            item('Coke', 1, 'beverage', { status: 'ready' }),
        ],
    },
    {
        kot: 1063, round: 1, orderCode: 'ORD-1056', orderType: 'takeaway', table: null, token: 'T109',
        waiter: null, guests: null, placedMinutesAgo: 16, acceptedMinutesAgo: 15, startedMinutesAgo: 14,
        status: 'preparing', priority: 'normal',
        items: [item('Hyderabadi Dum Gosht Biryani (Handi Special)', 1, 'main', { allergy: 'ALLERGY: CASHEW', status: 'pending' })],
    },
    {
        kot: 1064, round: 1, orderCode: 'ORD-1057', orderType: 'dinein', table: 'F09',
        waiter: 'Nabila', guests: 2, placedMinutesAgo: 22, acceptedMinutesAgo: 21, startedMinutesAgo: 19,
        status: 'preparing', priority: 'normal',
        items: [
            item('Kadai Chicken', 1, 'main', { status: 'pending' }),
            item('Garlic Naan', 2, 'tandoor', { status: 'ready' }),
        ],
    },
    {
        kot: 1065, round: 1, orderCode: 'ORD-1058', orderType: 'dinein', table: 'T09',
        waiter: 'Suman', guests: 2, placedMinutesAgo: 32, acceptedMinutesAgo: 31, startedMinutesAgo: 29,
        status: 'preparing', priority: 'rush',
        items: [
            item('Prawn Biryani', 1, 'main', { status: 'pending', note: 'Guest arrived early — please expedite' }),
            item('Fresh Lime Soda', 2, 'beverage', { status: 'ready' }),
        ],
    },
    {
        kot: 1066, round: 1, orderCode: 'ORD-1059', orderType: 'delivery', table: null, token: 'D505',
        waiter: null, guests: null, placedMinutesAgo: 10, acceptedMinutesAgo: 9, startedMinutesAgo: 8,
        status: 'preparing', priority: 'normal',
        items: [
            item('Chilli Paneer Dry', 1, 'chinese', { status: 'ready' }),
            item('Schezwan Fried Rice', 1, 'chinese', { status: 'pending' }),
            item('Mutton Rogan Josh', 1, 'main', { status: 'cancel_requested', note: 'Cancel request from POS — guest changed mind' }),
        ],
    },
    {
        kot: 1067, round: 1, orderCode: 'ORD-1060', orderType: 'dinein', table: 'V01',
        waiter: 'Ankit', guests: 5, placedMinutesAgo: 18, acceptedMinutesAgo: 17, startedMinutesAgo: 15,
        status: 'preparing', priority: 'vip',
        items: [
            item('Tandoori Pomfret', 1, 'tandoor', { status: 'pending' }),
            item('Mutton Rogan Josh', 2, 'main', { status: 'pending' }),
        ],
    },

    // ---------------------------------------------------------------- READY
    {
        kot: 1043, round: 1, orderCode: 'ORD-1061', orderType: 'dinein', table: 'T04',
        waiter: 'Ankit', guests: 2, placedMinutesAgo: 16, acceptedMinutesAgo: 15, startedMinutesAgo: 13, readyMinutesAgo: 2,
        status: 'ready', priority: 'normal', waiterNotified: true,
        items: [item('Chicken Biryani', 2, 'main', { status: 'ready' }), item('Veg Fried Rice', 1, 'chinese', { status: 'ready' })],
    },
    {
        kot: 1068, round: 1, orderCode: 'ORD-1062', orderType: 'takeaway', table: null, token: 'T110',
        waiter: null, guests: null, placedMinutesAgo: 14, acceptedMinutesAgo: 13, startedMinutesAgo: 11, readyMinutesAgo: 1,
        status: 'ready', priority: 'normal', waiterNotified: false,
        items: [item('Chicken Fried Rice', 2, 'chinese', { status: 'ready' })],
    },
    {
        kot: 1069, round: 1, orderCode: 'ORD-1063', orderType: 'dinein', table: 'T19',
        waiter: 'Rahul', guests: 4, placedMinutesAgo: 20, acceptedMinutesAgo: 19, startedMinutesAgo: 17, readyMinutesAgo: 5,
        status: 'ready', priority: 'normal', waiterNotified: true,
        items: [item('Tandoori Chicken', 1, 'tandoor', { status: 'ready' }), item('Butter Naan', 4, 'tandoor', { status: 'ready' })],
    },
    {
        kot: 1070, round: 1, orderCode: 'ORD-1064', orderType: 'delivery', table: null, token: 'D506',
        waiter: null, guests: null, placedMinutesAgo: 9, acceptedMinutesAgo: 8, startedMinutesAgo: 6, readyMinutesAgo: 1,
        status: 'ready', priority: 'normal', waiterNotified: false,
        items: [item('Veg Thali', 1, 'main', { status: 'ready' })],
    },
    {
        kot: 1071, round: 1, orderCode: 'ORD-1065', orderType: 'dinein', table: 'F04',
        waiter: 'Nabila', guests: 6, placedMinutesAgo: 24, acceptedMinutesAgo: 23, startedMinutesAgo: 21, readyMinutesAgo: 3,
        status: 'ready', priority: 'normal', waiterNotified: true,
        items: [item('Non-Veg Thali', 2, 'main', { status: 'ready' }), item('Gulab Jamun (2 pcs)', 2, 'dessert', { status: 'ready' })],
    },
];
