export const VENUE = { name: 'Royal Bengal Restaurant', branch: 'Ichapur Main Branch' };

export const CATEGORIES = [
    { key: 'starters', label: 'Starters' },
    { key: 'biryani', label: 'Biryani' },
    { key: 'indian', label: 'Indian' },
    { key: 'chinese', label: 'Chinese' },
    { key: 'tandoor', label: 'Tandoor' },
    { key: 'rice', label: 'Rice' },
    { key: 'bread', label: 'Bread' },
    { key: 'desserts', label: 'Desserts' },
    { key: 'beverages', label: 'Beverages' },
    { key: 'combos', label: 'Combos' },
];

export const STATIONS = [
    { key: 'main', label: 'Main Kitchen' },
    { key: 'tandoor', label: 'Tandoor' },
    { key: 'chinese', label: 'Chinese' },
    { key: 'beverage', label: 'Beverage' },
    { key: 'dessert', label: 'Dessert' },
    { key: 'bar', label: 'Bar' },
];

export const TAX_PROFILES = ['GST 5%', 'GST 12%', 'GST 18%', 'Exempt'];

export const MODIFIER_GROUPS = [
    { id: 'spice', label: 'Spice Level', type: 'single', required: true, min: 1, max: 1, options: [{ label: 'Mild', delta: 0 }, { label: 'Medium', delta: 0 }, { label: 'Spicy', delta: 0 }] },
    { id: 'addons-biryani', label: 'Add-Ons', type: 'multi', required: false, min: 0, max: 4, options: [{ label: 'Extra Chicken', delta: 100 }, { label: 'Extra Egg', delta: 30 }, { label: 'Extra Gravy', delta: 40 }] },
    { id: 'burger-size', label: 'Size', type: 'single', required: true, min: 1, max: 1, options: [{ label: 'Regular', delta: 0 }, { label: 'Large', delta: 60 }] },
];

let uid = 1;
const item = (o) => ({
    id: 'ITM' + uid++, shortName: '', description: '', image: null, taxProfile: 'GST 5%', featured: false, popular: false,
    stockTracked: false, variants: [], modifierGroupIds: [], availability: 'available', ...o,
});

export const ITEMS = [
    item({ sku: 'BRY-001', name: 'Chicken Biryani', category: 'biryani', dietType: 'nonveg', price: 320, prepTime: 18, station: 'main', popular: true, stockTracked: true, variants: [{ label: 'Regular', price: 320 }, { label: 'Large', price: 420 }, { label: 'Family', price: 720 }], modifierGroupIds: ['spice', 'addons-biryani'] }),
    item({ sku: 'BRY-002', name: 'Mutton Biryani', category: 'biryani', dietType: 'nonveg', price: 420, prepTime: 22, station: 'main', stockTracked: true, modifierGroupIds: ['spice'] }),
    item({ sku: 'STR-001', name: 'Paneer Tikka', category: 'starters', dietType: 'veg', price: 280, prepTime: 15, station: 'tandoor', popular: true, modifierGroupIds: ['spice'] }),
    item({ sku: 'STR-002', name: 'Chicken Tikka', category: 'starters', dietType: 'nonveg', price: 360, prepTime: 16, station: 'tandoor', modifierGroupIds: ['spice'] }),
    item({ sku: 'CHN-001', name: 'Veg Fried Rice', category: 'chinese', dietType: 'veg', price: 220, prepTime: 12, station: 'chinese' }),
    item({ sku: 'CHN-002', name: 'Chicken Fried Rice', category: 'chinese', dietType: 'nonveg', price: 280, prepTime: 12, station: 'chinese', popular: true }),
    item({ sku: 'CHN-003', name: 'Chilli Chicken', category: 'chinese', dietType: 'nonveg', price: 340, prepTime: 14, station: 'chinese', availability: 'sold_out' }),
    item({ sku: 'BRD-001', name: 'Butter Naan', category: 'bread', dietType: 'veg', price: 50, prepTime: 8, station: 'tandoor', popular: true },),
    item({ sku: 'BEV-001', name: 'Coke', category: 'beverages', dietType: 'veg', price: 60, prepTime: 2, station: 'beverage' }),
    item({ sku: 'BEV-002', name: 'Tea', category: 'beverages', dietType: 'veg', price: 30, prepTime: 4, station: 'beverage' }),
    item({ sku: 'BEV-003', name: 'Coffee', category: 'beverages', dietType: 'veg', price: 50, prepTime: 4, station: 'beverage' }),
    item({ sku: 'DES-001', name: 'Ice Cream', category: 'desserts', dietType: 'veg', price: 120, prepTime: 3, station: 'dessert', availability: 'temp_unavailable' }),
    item({ sku: 'IND-001', name: 'Dal Makhani', category: 'indian', dietType: 'veg', price: 240, prepTime: 14, station: 'main' }),
    item({ sku: 'IND-002', name: 'Butter Chicken', category: 'indian', dietType: 'nonveg', price: 380, prepTime: 18, station: 'main', featured: true, modifierGroupIds: ['spice'] }),
    item({ sku: 'RIC-001', name: 'Steamed Rice', category: 'rice', dietType: 'veg', price: 120, prepTime: 8, station: 'main' }),
    item({ sku: 'CMB-001', name: 'Chicken Burger', category: 'combos', dietType: 'nonveg', price: 220, prepTime: 12, station: 'main', modifierGroupIds: ['burger-size'] }),
];

export const COMBOS = [
    { id: 'CB1', name: 'Biryani Combo', items: [{ name: 'Chicken Biryani', qty: 1 }, { name: 'Coke', qty: 1 }, { name: 'Raita', qty: 1 }], price: 399 },
    { id: 'CB2', name: 'Family Feast', items: [{ name: 'Mutton Biryani', qty: 2 }, { name: 'Butter Naan', qty: 4 }, { name: 'Coke', qty: 2 }], price: 1299 },
];
