/**
 * Reservations — demo data. Presentation-layer fixtures only, same spirit as
 * the POS/Floor/KDS/Billing demo data files.
 */

export const VENUE = { name: 'Royal Bengal Restaurant', branch: 'Ichapur Main Branch' };
export const OPERATOR = { name: 'Rakesh Singh', role: 'Restaurant Manager', initials: 'RS' };

export const FLOORS = [
    { key: 'ground', label: 'Ground Floor' },
    { key: 'first', label: 'First Floor' },
    { key: 'outdoor', label: 'Outdoor' },
    { key: 'vip', label: 'VIP Section' },
];

export const TABLES = [
    { id: 'T01', floor: 'ground', seats: 4 }, { id: 'T06', floor: 'ground', seats: 2 }, { id: 'T07', floor: 'ground', seats: 6 }, { id: 'T10', floor: 'ground', seats: 8 },
    { id: 'T11', floor: 'first', seats: 4 }, { id: 'T14', floor: 'first', seats: 2 }, { id: 'T16', floor: 'first', seats: 4 }, { id: 'T18', floor: 'first', seats: 4 },
    { id: 'T19', floor: 'outdoor', seats: 4 }, { id: 'T21', floor: 'outdoor', seats: 4 },
    { id: 'V03', floor: 'vip', seats: 6 },
];

export const SOURCES = ['Phone', 'Walk-in', 'Website', 'WhatsApp', 'Other'];
export const OCCASIONS = ['None', 'Birthday Dinner', 'Anniversary', 'Business Meet', 'Family Gathering', 'Date Night'];
export const WAITERS = ['Rahul Das', 'Ankit Roy', 'Suman Ghosh', 'Priya Sen'];

/** ISO date helpers so "today" is always genuinely today, not a stale hardcoded string. */
const today = new Date();
const iso = (offsetDays) => {
    const d = new Date(today);
    d.setDate(d.getDate() + offsetDays);
    return d.toISOString().slice(0, 10);
};

let seq = 200;
const res = (overrides) => ({
    id: 'RES-' + seq++,
    customer: 'Guest',
    phone: '9800000000',
    email: '',
    date: iso(0),
    time: '19:00',
    guests: 2,
    floor: 'ground',
    table: null,
    status: 'confirmed',
    occasion: 'None',
    request: '',
    source: 'Phone',
    deposit: 0,
    createdBy: 'Rakesh Singh',
    history: [{ at: 'Today 11:02 AM', text: 'Reservation created via Phone' }],
    ...overrides,
});

/** Today's list — deliberately totals 14, with 8 confirmed / 2 arrived / 3 seated / 1 no-show. */
export const RESERVATIONS = [
    res({ customer: 'Amit Roy', phone: '9830112245', date: iso(0), time: '13:00', guests: 4, floor: 'ground', table: 'T01', status: 'completed', occasion: 'None', source: 'Phone' }),
    res({ customer: 'Priya Das', phone: '9007556621', date: iso(0), time: '13:30', guests: 2, floor: 'ground', table: 'T06', status: 'completed', source: 'Walk-in' }),
    res({ customer: 'Rahul Sen', phone: '9836774410', date: iso(0), time: '18:30', guests: 3, floor: 'first', table: 'T11', status: 'seated', source: 'Website' }),
    res({ customer: 'Nirmal Chakraborty', phone: '9830112244', date: iso(0), time: '19:00', guests: 4, floor: 'ground', table: 'T07', status: 'seated', occasion: 'None', request: 'Window side seating', source: 'Phone' }),
    res({ customer: 'Arjun Sen', phone: '9748899001', date: iso(0), time: '19:15', guests: 6, floor: 'vip', table: 'V03', status: 'seated', occasion: 'Business Meet', source: 'Phone' }),
    res({ customer: 'Amit Roy', phone: '9830112245', date: iso(0), time: '19:30', guests: 6, floor: 'ground', table: 'T03', status: 'confirmed', occasion: 'Birthday Dinner', request: 'Cake arranged, please bring after mains', source: 'Phone', deposit: 500 }),
    res({ customer: 'S. Sen', phone: '9830011223', date: iso(0), time: '19:45', guests: 4, floor: 'first', table: null, status: 'confirmed', source: 'WhatsApp' }),
    res({ customer: 'K. Iyer', phone: '9748899001', date: iso(0), time: '20:15', guests: 5, floor: 'first', table: null, status: 'confirmed', request: 'Prefers window side', source: 'Website' }),
    res({ customer: 'F. Ali', phone: '9903448821', date: iso(0), time: '20:00', guests: 4, floor: 'outdoor', table: null, status: 'confirmed', source: 'Phone' }),
    res({ customer: 'Sarkar Family', phone: '9007556621', date: iso(0), time: '21:00', guests: 8, floor: 'vip', table: null, status: 'confirmed', occasion: 'Anniversary', request: 'Cake arranged', source: 'Phone', deposit: 1000 }),
    res({ customer: 'Deb Family', phone: '9830099887', date: iso(0), time: '20:30', guests: 5, floor: 'ground', table: null, status: 'confirmed', source: 'Phone' }),
    res({ customer: 'Ghosh Party', phone: '9830011999', date: iso(0), time: '20:45', guests: 8, floor: 'ground', table: null, status: 'confirmed', source: 'Phone' }),
    res({ customer: 'Roy Chowdhury', phone: '9830022888', date: iso(0), time: '19:20', guests: 5, floor: 'vip', table: null, status: 'arrived', source: 'Phone' }),
    res({ customer: 'Walk-in Guest', phone: '9830033777', date: iso(0), time: '18:00', guests: 2, floor: 'ground', table: null, status: 'arrived', source: 'Walk-in' }),
    res({ customer: 'B. Chatterjee', phone: '9830044666', date: iso(0), time: '18:00', guests: 3, floor: 'first', table: null, status: 'no_show', source: 'Phone' }),

    // Upcoming (tomorrow onward), for the LIST/Calendar views
    res({ customer: 'Imtiaz Rahman', phone: '9836774410', date: iso(1), time: '20:00', guests: 4, floor: 'vip', table: null, status: 'confirmed', occasion: 'Anniversary', source: 'Website' }),
    res({ customer: 'Ananya Dutta', phone: '9007556621', date: iso(1), time: '13:30', guests: 2, floor: 'ground', table: null, status: 'pending', source: 'WhatsApp' }),
    res({ customer: 'Corporate — Tata Steel', phone: '9830022111', date: iso(2), time: '20:00', guests: 12, floor: 'ground', table: null, status: 'confirmed', occasion: 'Business Meet', source: 'Phone', deposit: 2000 }),
    res({ customer: 'Farhan Ali', phone: '9903448821', date: iso(3), time: '19:30', guests: 2, floor: 'outdoor', table: null, status: 'pending', source: 'Other' }),
    res({ customer: 'Rohit Sharma', phone: '9748110034', date: iso(5), time: '20:30', guests: 4, floor: 'first', table: null, status: 'confirmed', source: 'Phone' }),
    res({ customer: 'Priya Das', phone: '9007556621', date: iso(-1), time: '19:00', guests: 2, floor: 'ground', table: 'T06', status: 'cancelled', source: 'Phone', history: [{ at: 'Yesterday 10:00 AM', text: 'Reservation created via Phone' }, { at: 'Yesterday 6:40 PM', text: 'Cancelled — guest called to cancel' }] }),
];

export const CANCEL_REASONS = ['Guest requested', 'Duplicate booking', 'Table unavailable', 'Weather / travel', 'Other'];
