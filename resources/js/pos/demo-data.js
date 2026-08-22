/**
 * POS demo data.
 *
 * This is presentation-layer fixture data only — it exists so the layout can be
 * judged against realistic volume (50 menu items, a 13-line order spanning two
 * KOT rounds, 28 tables, long names, modifiers, sold-out stock). When the
 * backend lands, every export here is replaced by a server payload; nothing in
 * the UI reads these files directly except the Alpine root.
 */

export const VENUE = {
    name: 'Royal Bengal Restaurant',
    branch: 'Ichapur Main Branch',
    terminal: 'POS-01',
    initials: 'RB',
    gstin: '19AABCR1234K1Z8',
    address: '18 Grand Trunk Road, Ichapur, North 24 Parganas, WB 743144',
    phone: '+91 33 2593 4400',
};

export const OPERATOR = {
    name: 'Rahul',
    fullName: 'Rahul Sengupta',
    role: 'Waiter',
    initials: 'R',
    shift: 'OPEN',
    shiftStartedAt: '16:30',
    /** Discounts above this need a manager PIN — drives ManagerApprovalModal. */
    discountLimitPct: 10,
};

export const CATEGORIES = [
    { key: 'all', label: 'All Items', icon: 'grid', pinned: true },
    { key: 'favorites', label: 'Favorites', icon: 'star', pinned: true },
    { key: 'recent', label: 'Recent', icon: 'clock', pinned: true },
    { key: 'starters', label: 'Starters', icon: 'starter' },
    { key: 'biryani', label: 'Biryani', icon: 'biryani' },
    { key: 'indian', label: 'Indian', icon: 'curry' },
    { key: 'chinese', label: 'Chinese', icon: 'noodle' },
    { key: 'tandoor', label: 'Tandoor', icon: 'flame' },
    { key: 'rice', label: 'Rice', icon: 'rice' },
    { key: 'bread', label: 'Bread', icon: 'bread' },
    { key: 'desserts', label: 'Dessert', icon: 'dessert' },
    { key: 'beverages', label: 'Beverages', icon: 'drink' },
    { key: 'combos', label: 'Combos', icon: 'combo' },
];

/* Reusable modifier groups, referenced by id from menu items. */
export const MODIFIER_GROUPS = {
    portion: {
        id: 'portion',
        label: 'Portion',
        type: 'single',
        required: true,
        options: [
            { id: 'half', label: 'Half', delta: 0 },
            { id: 'full', label: 'Full', delta: 160 },
        ],
    },
    burgerSize: {
        id: 'burgerSize',
        label: 'Size',
        type: 'single',
        required: true,
        options: [
            { id: 'reg', label: 'Regular', delta: 0 },
            { id: 'large', label: 'Large', delta: 60 },
        ],
    },
    biryaniCut: {
        id: 'biryaniCut',
        label: 'Cut',
        type: 'single',
        required: true,
        options: [
            { id: 'mixed', label: 'Mixed', delta: 0 },
            { id: 'leg', label: 'Leg Piece', delta: 40 },
            { id: 'boneless', label: 'Boneless', delta: 60 },
        ],
    },
    spice: {
        id: 'spice',
        label: 'Spice Level',
        type: 'single',
        required: false,
        options: [
            { id: 'mild', label: 'Mild', delta: 0 },
            { id: 'medium', label: 'Medium', delta: 0 },
            { id: 'hot', label: 'Hot', delta: 0 },
        ],
    },
    extras: {
        id: 'extras',
        label: 'Extras',
        type: 'multi',
        required: false,
        options: [
            { id: 'cheese', label: 'Extra Cheese', delta: 30 },
            { id: 'chicken', label: 'Extra Chicken', delta: 70 },
            { id: 'sauce', label: 'Extra Sauce', delta: 20 },
            { id: 'raita', label: 'Extra Raita', delta: 25 },
        ],
    },
    scoops: {
        id: 'scoops',
        label: 'Flavour',
        type: 'single',
        required: true,
        options: [
            { id: 'vanilla', label: 'Vanilla', delta: 0 },
            { id: 'choco', label: 'Belgian Chocolate', delta: 40 },
            { id: 'butterscotch', label: 'Butterscotch', delta: 30 },
        ],
    },
};

/**
 * diet: 'veg' | 'nonveg' | 'egg'
 * stock: 'in' | 'low' | 'out'
 * mods:  ids into MODIFIER_GROUPS; a required group forces the configurator
 */
export const MENU = [
    // Starters
    { id: 'ST01', code: 'ST01', name: 'Paneer Tikka', price: 280, cat: 'starters', diet: 'veg', station: 'Tandoor', prep: 15, stock: 'in', fav: true, mods: ['portion', 'spice'] },
    { id: 'ST02', code: 'ST02', name: 'Chicken Tikka', price: 320, cat: 'starters', diet: 'nonveg', station: 'Tandoor', prep: 16, stock: 'in', fav: true, mods: ['portion', 'spice'] },
    { id: 'ST03', code: 'ST03', name: 'Veg Spring Roll', price: 180, cat: 'starters', diet: 'veg', station: 'Chinese', prep: 10, stock: 'in' },
    { id: 'ST04', code: 'ST04', name: 'Chilli Paneer Dry', price: 260, cat: 'starters', diet: 'veg', station: 'Chinese', prep: 12, stock: 'in' },
    { id: 'ST05', code: 'ST05', name: 'Crispy Fish Fingers', price: 340, cat: 'starters', diet: 'nonveg', station: 'Main', prep: 14, stock: 'low', left: 3 },
    { id: 'ST06', code: 'ST06', name: 'Mushroom Galouti Kebab', price: 290, cat: 'starters', diet: 'veg', station: 'Tandoor', prep: 18, stock: 'in' },
    { id: 'ST07', code: 'ST07', name: 'Chicken Lollipop', price: 300, cat: 'starters', diet: 'nonveg', station: 'Chinese', prep: 15, stock: 'in' },
    { id: 'ST08', code: 'ST08', name: 'Honey Chilli Potato', price: 190, cat: 'starters', diet: 'veg', station: 'Chinese', prep: 10, stock: 'in' },
    { id: 'ST09', code: 'ST09', name: 'Egg Devil (2 pcs)', price: 160, cat: 'starters', diet: 'egg', station: 'Main', prep: 12, stock: 'in' },

    // Biryani
    { id: 'BR01', code: 'BR01', name: 'Chicken Biryani', price: 320, cat: 'biryani', diet: 'nonveg', station: 'Main', prep: 18, stock: 'in', fav: true, mods: ['biryaniCut', 'spice', 'extras'] },
    { id: 'BR02', code: 'BR02', name: 'Mutton Biryani', price: 420, cat: 'biryani', diet: 'nonveg', station: 'Main', prep: 22, stock: 'in', fav: true, mods: ['spice', 'extras'] },
    { id: 'BR03', code: 'BR03', name: 'Hyderabadi Dum Gosht Biryani (Handi Special)', price: 480, cat: 'biryani', diet: 'nonveg', station: 'Main', prep: 28, stock: 'low', left: 2, mods: ['spice'] },
    { id: 'BR04', code: 'BR04', name: 'Veg Biryani', price: 240, cat: 'biryani', diet: 'veg', station: 'Main', prep: 16, stock: 'in' },
    { id: 'BR05', code: 'BR05', name: 'Egg Biryani', price: 220, cat: 'biryani', diet: 'egg', station: 'Main', prep: 16, stock: 'in' },
    { id: 'BR06', code: 'BR06', name: 'Prawn Biryani', price: 480, cat: 'biryani', diet: 'nonveg', station: 'Main', prep: 24, stock: 'out' },

    // Indian
    { id: 'IN01', code: 'IN01', name: 'Butter Chicken', price: 380, cat: 'indian', diet: 'nonveg', station: 'Main', prep: 18, stock: 'in', fav: true, mods: ['portion', 'spice'] },
    { id: 'IN02', code: 'IN02', name: 'Dal Makhani', price: 240, cat: 'indian', diet: 'veg', station: 'Main', prep: 14, stock: 'in', fav: true },
    { id: 'IN03', code: 'IN03', name: 'Paneer Butter Masala', price: 300, cat: 'indian', diet: 'veg', station: 'Main', prep: 16, stock: 'in' },
    { id: 'IN04', code: 'IN04', name: 'Kadai Chicken', price: 350, cat: 'indian', diet: 'nonveg', station: 'Main', prep: 20, stock: 'in', mods: ['spice'] },
    { id: 'IN05', code: 'IN05', name: 'Mutton Rogan Josh', price: 450, cat: 'indian', diet: 'nonveg', station: 'Main', prep: 26, stock: 'out' },
    { id: 'IN06', code: 'IN06', name: 'Chana Masala', price: 200, cat: 'indian', diet: 'veg', station: 'Main', prep: 12, stock: 'in' },
    { id: 'IN07', code: 'IN07', name: 'Palak Paneer', price: 280, cat: 'indian', diet: 'veg', station: 'Main', prep: 15, stock: 'in' },
    { id: 'IN08', code: 'IN08', name: 'Fish Kalia (Bengali Style)', price: 400, cat: 'indian', diet: 'nonveg', station: 'Main', prep: 22, stock: 'in' },

    // Chinese
    { id: 'CH01', code: 'CH01', name: 'Veg Hakka Noodles', price: 210, cat: 'chinese', diet: 'veg', station: 'Chinese', prep: 12, stock: 'in' },
    { id: 'CH02', code: 'CH02', name: 'Chicken Fried Rice', price: 240, cat: 'chinese', diet: 'nonveg', station: 'Chinese', prep: 12, stock: 'in', fav: true },
    { id: 'CH03', code: 'CH03', name: 'Chilli Chicken', price: 290, cat: 'chinese', diet: 'nonveg', station: 'Chinese', prep: 14, stock: 'in', fav: true, mods: ['portion', 'spice'] },
    { id: 'CH04', code: 'CH04', name: 'Veg Manchurian Gravy', price: 220, cat: 'chinese', diet: 'veg', station: 'Chinese', prep: 13, stock: 'in' },
    { id: 'CH05', code: 'CH05', name: 'Schezwan Fried Rice', price: 230, cat: 'chinese', diet: 'veg', station: 'Chinese', prep: 12, stock: 'in', mods: ['spice'] },
    { id: 'CH06', code: 'CH06', name: 'American Chopsuey', price: 260, cat: 'chinese', diet: 'egg', station: 'Chinese', prep: 15, stock: 'low', left: 4 },

    // Tandoor
    { id: 'TN01', code: 'TN01', name: 'Tandoori Chicken', price: 360, cat: 'tandoor', diet: 'nonveg', station: 'Tandoor', prep: 25, stock: 'in', mods: ['portion'] },
    { id: 'TN02', code: 'TN02', name: 'Murgh Malai Tikka', price: 340, cat: 'tandoor', diet: 'nonveg', station: 'Tandoor', prep: 20, stock: 'in' },
    { id: 'TN03', code: 'TN03', name: 'Mutton Seekh Kebab', price: 320, cat: 'tandoor', diet: 'nonveg', station: 'Tandoor', prep: 18, stock: 'in' },
    { id: 'TN04', code: 'TN04', name: 'Tandoori Pomfret', price: 620, cat: 'tandoor', diet: 'nonveg', station: 'Tandoor', prep: 30, stock: 'low', left: 2 },

    // Rice
    { id: 'RC01', code: 'RC01', name: 'Steamed Rice', price: 120, cat: 'rice', diet: 'veg', station: 'Main', prep: 8, stock: 'in' },
    { id: 'RC02', code: 'RC02', name: 'Jeera Rice', price: 160, cat: 'rice', diet: 'veg', station: 'Main', prep: 10, stock: 'in' },
    { id: 'RC03', code: 'RC03', name: 'Ghee Rice', price: 180, cat: 'rice', diet: 'veg', station: 'Main', prep: 10, stock: 'in' },

    // Bread
    { id: 'BD01', code: 'BD01', name: 'Butter Naan', price: 50, cat: 'bread', diet: 'veg', station: 'Tandoor', prep: 8, stock: 'in', fav: true },
    { id: 'BD02', code: 'BD02', name: 'Garlic Naan', price: 70, cat: 'bread', diet: 'veg', station: 'Tandoor', prep: 8, stock: 'in', fav: true },
    { id: 'BD03', code: 'BD03', name: 'Tandoori Roti', price: 30, cat: 'bread', diet: 'veg', station: 'Tandoor', prep: 6, stock: 'in' },
    { id: 'BD04', code: 'BD04', name: 'Laccha Paratha', price: 70, cat: 'bread', diet: 'veg', station: 'Tandoor', prep: 9, stock: 'in' },
    { id: 'BD05', code: 'BD05', name: 'Cheese Chilli Naan', price: 110, cat: 'bread', diet: 'veg', station: 'Tandoor', prep: 10, stock: 'in' },

    // Dessert
    { id: 'DS01', code: 'DS01', name: 'Gulab Jamun (2 pcs)', price: 90, cat: 'desserts', diet: 'veg', station: 'Pantry', prep: 4, stock: 'in' },
    { id: 'DS02', code: 'DS02', name: 'Ice Cream Scoop', price: 120, cat: 'desserts', diet: 'veg', station: 'Pantry', prep: 3, stock: 'in', mods: ['scoops'] },
    { id: 'DS03', code: 'DS03', name: 'Rasmalai', price: 140, cat: 'desserts', diet: 'veg', station: 'Pantry', prep: 4, stock: 'in' },
    { id: 'DS04', code: 'DS04', name: 'Baked Rasgulla', price: 160, cat: 'desserts', diet: 'veg', station: 'Pantry', prep: 12, stock: 'low', left: 5 },

    // Beverages
    { id: 'BV01', code: 'BV01', name: 'Coke (300 ml)', price: 60, cat: 'beverages', diet: 'veg', station: 'Pantry', prep: 2, stock: 'in', fav: true },
    { id: 'BV02', code: 'BV02', name: 'Fresh Lime Soda', price: 90, cat: 'beverages', diet: 'veg', station: 'Pantry', prep: 4, stock: 'in' },
    { id: 'BV03', code: 'BV03', name: 'Masala Coffee', price: 80, cat: 'beverages', diet: 'veg', station: 'Pantry', prep: 5, stock: 'in' },
    { id: 'BV04', code: 'BV04', name: 'Sweet Lassi', price: 110, cat: 'beverages', diet: 'veg', station: 'Pantry', prep: 5, stock: 'in' },
    { id: 'BV05', code: 'BV05', name: 'Mineral Water (1 L)', price: 20, cat: 'beverages', diet: 'veg', station: 'Pantry', prep: 1, stock: 'in' },
    { id: 'BV06', code: 'BV06', name: 'Cold Coffee with Ice Cream', price: 140, cat: 'beverages', diet: 'veg', station: 'Pantry', prep: 6, stock: 'in' },

    // Combos
    { id: 'CB01', code: 'CB01', name: 'Biryani Combo Meal', price: 399, cat: 'combos', diet: 'nonveg', station: 'Main', prep: 20, stock: 'in', mods: ['biryaniCut', 'extras'] },
    { id: 'CB02', code: 'CB02', name: 'Veg Thali', price: 320, cat: 'combos', diet: 'veg', station: 'Main', prep: 18, stock: 'in' },
    { id: 'CB03', code: 'CB03', name: 'Non-Veg Thali', price: 420, cat: 'combos', diet: 'nonveg', station: 'Main', prep: 20, stock: 'in' },
    { id: 'CB04', code: 'CB04', name: 'Chicken Burger', price: 220, cat: 'combos', diet: 'nonveg', station: 'Main', prep: 12, stock: 'in', mods: ['burgerSize', 'extras'] },
];

export const RECENT_IDS = ['BR01', 'BD01', 'BV01', 'IN01', 'ST01', 'BD02', 'DS01', 'CH03'];

/**
 * Seed order: 13 lines across two dispatched KOT rounds plus an unsent round.
 * Deliberately includes a cancelled line, modifiers, notes and a long name so
 * the cart renders at realistic worst case.
 */
export const SEED_CART = [
    { uid: 101, ref: 'BR01', name: 'Chicken Biryani', price: 320, qty: 2, diet: 'nonveg', station: 'Main', variant: 'Boneless', modifiers: [{ label: 'Extra Raita', delta: 25 }], note: 'Less Spicy', status: 'preparing', kot: 1024, sentAt: '19:12' },
    { uid: 102, ref: 'ST01', name: 'Paneer Tikka', price: 280, qty: 1, diet: 'veg', station: 'Tandoor', variant: 'Half', modifiers: [], note: '', status: 'ready', kot: 1024, sentAt: '19:12' },
    { uid: 103, ref: 'BV01', name: 'Coke (300 ml)', price: 60, qty: 2, diet: 'veg', station: 'Pantry', modifiers: [], note: 'Chilled, no ice', status: 'served', kot: 1024, sentAt: '19:12' },
    { uid: 104, ref: 'BD03', name: 'Tandoori Roti', price: 30, qty: 4, diet: 'veg', station: 'Tandoor', modifiers: [], note: '', status: 'served', kot: 1024, sentAt: '19:12' },
    { uid: 105, ref: 'IN05', name: 'Mutton Rogan Josh', price: 450, qty: 1, diet: 'nonveg', station: 'Main', modifiers: [], note: '', status: 'cancelled', kot: 1024, sentAt: '19:12', cancelReason: 'Out of stock — informed guest' },

    { uid: 106, ref: 'IN01', name: 'Butter Chicken', price: 380, qty: 1, diet: 'nonveg', station: 'Main', variant: 'Full', modifiers: [], note: 'Extra gravy on the side', status: 'sent', kot: 1045, sentAt: '19:24' },
    { uid: 107, ref: 'BD02', name: 'Garlic Naan', price: 70, qty: 3, diet: 'veg', station: 'Tandoor', modifiers: [], note: '', status: 'accepted', kot: 1045, sentAt: '19:24' },
    { uid: 108, ref: 'IN02', name: 'Dal Makhani', price: 240, qty: 1, diet: 'veg', station: 'Main', modifiers: [], note: '', status: 'preparing', kot: 1045, sentAt: '19:24' },
    { uid: 109, ref: 'BR03', name: 'Hyderabadi Dum Gosht Biryani (Handi Special)', price: 480, qty: 1, diet: 'nonveg', station: 'Main', variant: 'Medium', modifiers: [], note: 'Guest is allergic to cashew — no nuts', status: 'ready', kot: 1045, sentAt: '19:24' },

    { uid: 110, ref: 'BD01', name: 'Butter Naan', price: 50, qty: 4, diet: 'veg', station: 'Tandoor', modifiers: [], note: '', status: 'unsent', kot: null, sentAt: null },
    { uid: 111, ref: 'DS01', name: 'Gulab Jamun (2 pcs)', price: 90, qty: 2, diet: 'veg', station: 'Pantry', modifiers: [], note: 'Serve after mains', status: 'unsent', kot: null, sentAt: null },
    { uid: 112, ref: 'BV03', name: 'Masala Coffee', price: 80, qty: 2, diet: 'veg', station: 'Pantry', modifiers: [], note: '', status: 'unsent', kot: null, sentAt: null },
    { uid: 113, ref: 'DS02', name: 'Ice Cream Scoop', price: 120, qty: 1, diet: 'veg', station: 'Pantry', variant: 'Belgian Chocolate', modifiers: [{ label: 'Belgian Chocolate', delta: 40 }], note: '', status: 'unsent', kot: null, sentAt: null },
];

export const KOT_HISTORY = [
    { kot: 1024, round: 1, sentAt: '19:12', by: 'Rahul', printer: 'Main Kitchen + Tandoor', lines: [
        { name: 'Chicken Biryani', qty: 2, note: 'Boneless · Extra Raita · Less Spicy', state: 'PREPARING' },
        { name: 'Paneer Tikka', qty: 1, note: 'Half', state: 'READY' },
        { name: 'Coke (300 ml)', qty: 2, note: 'Chilled, no ice', state: 'SERVED' },
        { name: 'Tandoori Roti', qty: 4, note: '', state: 'SERVED' },
        { name: 'Mutton Rogan Josh', qty: 1, note: 'Cancelled — out of stock', state: 'CANCELLED' },
    ] },
    { kot: 1045, round: 2, sentAt: '19:24', by: 'Rahul', printer: 'Main Kitchen + Tandoor', lines: [
        { name: 'Butter Chicken', qty: 1, note: 'Full · Extra gravy on the side', state: 'SENT' },
        { name: 'Garlic Naan', qty: 3, note: '', state: 'ACCEPTED' },
        { name: 'Dal Makhani', qty: 1, note: '', state: 'PREPARING' },
        { name: 'Hyderabadi Dum Gosht Biryani', qty: 1, note: 'No nuts — guest allergy', state: 'READY' },
    ] },
];

export const FLOORS = [
    { key: 'ground', label: 'Ground Floor' },
    { key: 'first', label: 'First Floor' },
    { key: 'outdoor', label: 'Outdoor' },
];

/** status: available | reserved | occupied | billing | cleaning */
export const TABLES = [
    { id: 'T01', floor: 'ground', seats: 4, status: 'available' },
    { id: 'T02', floor: 'ground', seats: 2, status: 'occupied', amount: 1850, since: 34, guests: 2, waiter: 'Imran' },
    { id: 'T03', floor: 'ground', seats: 6, status: 'billing', amount: 2400, since: 62, guests: 5, waiter: 'Rahul' },
    { id: 'T04', floor: 'ground', seats: 4, status: 'occupied', amount: 1450, since: 22, guests: 4, waiter: 'Rahul' },
    { id: 'T05', floor: 'ground', seats: 4, status: 'reserved', reservedFor: '19:30', guestName: 'S. Banerjee' },
    { id: 'T06', floor: 'ground', seats: 2, status: 'available' },
    { id: 'T07', floor: 'ground', seats: 8, status: 'cleaning' },
    { id: 'T08', floor: 'ground', seats: 6, status: 'occupied', amount: 3060, since: 18, guests: 4, waiter: 'Rahul', current: true },
    { id: 'T09', floor: 'ground', seats: 4, status: 'available' },
    { id: 'T10', floor: 'ground', seats: 4, status: 'occupied', amount: 980, since: 11, guests: 3, waiter: 'Imran' },
    { id: 'T11', floor: 'ground', seats: 2, status: 'available' },
    { id: 'T12', floor: 'ground', seats: 10, status: 'reserved', reservedFor: '20:15', guestName: 'Corporate — Tata Steel' },

    { id: 'F01', floor: 'first', seats: 4, status: 'available' },
    { id: 'F02', floor: 'first', seats: 4, status: 'occupied', amount: 2210, since: 41, guests: 4, waiter: 'Nabila' },
    { id: 'F03', floor: 'first', seats: 6, status: 'available' },
    { id: 'F04', floor: 'first', seats: 6, status: 'billing', amount: 5120, since: 78, guests: 6, waiter: 'Nabila' },
    { id: 'F05', floor: 'first', seats: 2, status: 'available' },
    { id: 'F06', floor: 'first', seats: 4, status: 'cleaning' },
    { id: 'F07', floor: 'first', seats: 4, status: 'available' },
    { id: 'F08', floor: 'first', seats: 12, status: 'reserved', reservedFor: '21:00', guestName: 'Ghosh Anniversary' },
    { id: 'F09', floor: 'first', seats: 4, status: 'occupied', amount: 760, since: 8, guests: 2, waiter: 'Imran' },
    { id: 'F10', floor: 'first', seats: 4, status: 'available' },

    { id: 'O01', floor: 'outdoor', seats: 4, status: 'available' },
    { id: 'O02', floor: 'outdoor', seats: 4, status: 'occupied', amount: 1320, since: 27, guests: 4, waiter: 'Rahul' },
    { id: 'O03', floor: 'outdoor', seats: 6, status: 'available' },
    { id: 'O04', floor: 'outdoor', seats: 2, status: 'reserved', reservedFor: '20:00', guestName: 'A. Dutta' },
    { id: 'O05', floor: 'outdoor', seats: 4, status: 'cleaning' },
    { id: 'O06', floor: 'outdoor', seats: 8, status: 'available' },
];

export const CUSTOMERS = [
    { id: 'C1', name: 'Sourav Banerjee', phone: '9830112244', visits: 27, spend: 48600, points: 1240, tag: 'Gold' },
    { id: 'C2', name: 'Ananya Dutta', phone: '9007556621', visits: 9, spend: 12400, points: 310, tag: 'Silver' },
    { id: 'C3', name: 'Imtiaz Rahman', phone: '9836774410', visits: 41, spend: 96200, points: 2680, tag: 'Platinum' },
    { id: 'C4', name: 'Priya Ghosh', phone: '9163302299', visits: 4, spend: 5200, points: 120, tag: '' },
    { id: 'C5', name: 'Rohit Sharma', phone: '9748110034', visits: 15, spend: 27800, points: 690, tag: 'Silver' },
    { id: 'C6', name: 'Farhan Ali', phone: '9903448821', visits: 2, spend: 1900, points: 40, tag: '' },
];

export const RUNNING_ORDERS = [
    { code: 'ORD-1028', type: 'dinein', label: 'T08 · Ground', amount: 3060, mins: 18, guests: 4, waiter: 'Rahul', state: 'Running', current: true },
    { code: 'ORD-1021', type: 'dinein', label: 'T04 · Ground', amount: 1450, mins: 22, guests: 4, waiter: 'Rahul', state: 'Running' },
    { code: 'ORD-1019', type: 'dinein', label: 'T03 · Ground', amount: 2400, mins: 62, guests: 5, waiter: 'Rahul', state: 'Billing' },
    { code: 'ORD-1024', type: 'dinein', label: 'F02 · First', amount: 2210, mins: 41, guests: 4, waiter: 'Nabila', state: 'Running' },
    { code: 'TKA-0104', type: 'takeaway', label: 'Token 104 · A. Dutta', amount: 680, mins: 8, state: 'Preparing' },
    { code: 'TKA-0105', type: 'takeaway', label: 'Token 105 · Walk-in', amount: 240, mins: 3, state: 'New' },
    { code: 'DLV-0312', type: 'delivery', label: 'Own Rider · Ichapur', amount: 1180, mins: 12, state: 'Packed' },
    { code: 'DLV-0313', type: 'delivery', label: 'Aggregator · Zomato', amount: 940, mins: 5, state: 'Preparing' },
];

export const KITCHEN_LOAD = { new: 3, prep: 8, ready: 2 };

export const READY_ALERTS = [
    { id: 'A1', table: 'T08', item: 'Paneer Tikka', qty: 1, station: 'Tandoor' },
    { id: 'A2', table: 'T08', item: 'Hyderabadi Dum Gosht Biryani', qty: 1, station: 'Main Kitchen' },
];

export const WAITERS = ['Rahul', 'Imran', 'Nabila', 'Sujoy', 'Deepa'];

export const DISCOUNT_REASONS = [
    'Loyalty / Regular guest',
    'Manager complimentary',
    'Service delay',
    'Staff meal',
    'Promotional offer',
    'Corporate tie-up',
];

export const CANCEL_REASONS = [
    'Guest changed mind',
    'Out of stock',
    'Wrong item punched',
    'Excessive delay',
    'Quality issue',
    'Duplicate entry',
];

export const CHARGE_CONFIG = {
    taxLabel: 'GST',
    taxRate: 0.05,
    taxSplit: [
        { label: 'CGST', rate: 0.025 },
        { label: 'SGST', rate: 0.025 },
    ],
    serviceLabel: 'Service Charge',
    serviceRate: 0.05,
    serviceEnabled: true,
};

export const PAYMENT_METHODS = [
    { key: 'cash', label: 'Cash' },
    { key: 'upi', label: 'UPI' },
    { key: 'card', label: 'Card' },
    { key: 'wallet', label: 'Wallet' },
    { key: 'bank', label: 'Bank' },
    { key: 'other', label: 'Other' },
];

export const SHORTCUTS = [
    { keys: 'F1', label: 'New order' },
    { keys: 'F2', label: 'Focus product search' },
    { keys: 'F3', label: 'Customer lookup' },
    { keys: 'F4', label: 'Table selector' },
    { keys: 'F6', label: 'Send KOT' },
    { keys: 'F7', label: 'Discount' },
    { keys: 'F8', label: 'Bill preview' },
    { keys: 'F9', label: 'Payment' },
    { keys: 'F10', label: 'Running orders' },
    { keys: 'Esc', label: 'Close modal / clear search' },
    { keys: '?', label: 'This shortcut sheet' },
];
