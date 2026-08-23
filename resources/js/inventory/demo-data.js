export const VENUE = { name: 'Royal Bengal Restaurant', branch: 'Ichapur Main Branch' };

export const UNITS = ['KG', 'GRAM', 'LITRE', 'ML', 'PCS', 'PACK', 'BOX', 'BOTTLE'];
export const CATEGORIES = ['Grains & Rice', 'Meat & Poultry', 'Dairy', 'Vegetables', 'Oil & Fats', 'Bakery', 'Beverages', 'Spices'];
export const STORAGE_LOCATIONS = ['Dry Store', 'Cold Storage', 'Freezer', 'Bar Store', 'Kitchen Shelf'];
export const SUPPLIERS = ['Bengal Food Supplies', 'Fresh Chicken Traders', 'Kolkata Vegetable Market', 'Eastern Beverage Distributors'];

const ing = (o) => ({ status: o.current <= 0 ? 'out' : o.current < o.min ? 'low' : 'in', ...o });

export const INGREDIENTS = [
    ing({ id: 'ING-001', name: 'Basmati Rice', category: 'Grains & Rice', unit: 'KG', current: 42, min: 15, reorder: 25, avgCost: 95, supplier: 'Bengal Food Supplies', location: 'Dry Store' }),
    ing({ id: 'ING-002', name: 'Chicken', category: 'Meat & Poultry', unit: 'KG', current: 8, min: 20, reorder: 30, avgCost: 210, supplier: 'Fresh Chicken Traders', location: 'Cold Storage' }),
    ing({ id: 'ING-003', name: 'Mutton', category: 'Meat & Poultry', unit: 'KG', current: 14, min: 10, reorder: 15, avgCost: 620, supplier: 'Fresh Chicken Traders', location: 'Freezer' }),
    ing({ id: 'ING-004', name: 'Onion', category: 'Vegetables', unit: 'KG', current: 3, min: 15, reorder: 25, avgCost: 32, supplier: 'Kolkata Vegetable Market', location: 'Dry Store' }),
    ing({ id: 'ING-005', name: 'Tomato', category: 'Vegetables', unit: 'KG', current: 18, min: 10, reorder: 15, avgCost: 28, supplier: 'Kolkata Vegetable Market', location: 'Dry Store' }),
    ing({ id: 'ING-006', name: 'Cooking Oil', category: 'Oil & Fats', unit: 'LITRE', current: 22, min: 10, reorder: 15, avgCost: 145, supplier: 'Bengal Food Supplies', location: 'Dry Store' }),
    ing({ id: 'ING-007', name: 'Paneer', category: 'Dairy', unit: 'KG', current: 6, min: 8, reorder: 12, avgCost: 320, supplier: 'Bengal Food Supplies', location: 'Cold Storage' }),
    ing({ id: 'ING-008', name: 'Flour', category: 'Bakery', unit: 'KG', current: 30, min: 12, reorder: 20, avgCost: 42, supplier: 'Bengal Food Supplies', location: 'Dry Store' }),
    ing({ id: 'ING-009', name: 'Egg', category: 'Dairy', unit: 'PCS', current: 0, min: 60, reorder: 120, avgCost: 6, supplier: 'Bengal Food Supplies', location: 'Cold Storage' }),
    ing({ id: 'ING-010', name: 'Coke', category: 'Beverages', unit: 'BOTTLE', current: 48, min: 24, reorder: 48, avgCost: 38, supplier: 'Eastern Beverage Distributors', location: 'Bar Store' }),
    ing({ id: 'ING-011', name: 'Spices', category: 'Spices', unit: 'KG', current: 9, min: 4, reorder: 6, avgCost: 480, supplier: 'Bengal Food Supplies', location: 'Dry Store' }),
    ing({ id: 'ING-012', name: 'Milk', category: 'Dairy', unit: 'LITRE', current: 5, min: 10, reorder: 15, avgCost: 58, supplier: 'Bengal Food Supplies', location: 'Cold Storage' }),
];

export const RECIPES = {
    'Chicken Biryani': { sellPrice: 320, lines: [{ ingredient: 'Basmati Rice', qty: 0.25, unit: 'KG' }, { ingredient: 'Chicken', qty: 0.2, unit: 'KG' }, { ingredient: 'Onion', qty: 0.08, unit: 'KG' }, { ingredient: 'Cooking Oil', qty: 0.03, unit: 'LITRE' }, { ingredient: 'Spices', qty: 0.015, unit: 'KG' }] },
    'Butter Chicken': { sellPrice: 380, lines: [{ ingredient: 'Chicken', qty: 0.22, unit: 'KG' }, { ingredient: 'Tomato', qty: 0.15, unit: 'KG' }, { ingredient: 'Milk', qty: 0.05, unit: 'LITRE' }, { ingredient: 'Spices', qty: 0.02, unit: 'KG' }] },
};

export const LEDGER = [
    { at: '23/08/2026 08:00', ingredient: 'Basmati Rice', type: 'OPENING', ref: '—', prev: 0, change: 50, next: 50, user: 'Sourav Roy' },
    { at: '23/08/2026 10:15', ingredient: 'Basmati Rice', type: 'CONSUMPTION', ref: 'ORD-1028', prev: 50, change: -8, next: 42, user: 'System' },
    { at: '23/08/2026 09:00', ingredient: 'Chicken', type: 'PURCHASE', ref: 'GRN-2026-0042', prev: 4, change: 20, next: 24, user: 'Sourav Roy' },
    { at: '23/08/2026 13:30', ingredient: 'Chicken', type: 'CONSUMPTION', ref: 'ORD-1041', prev: 24, change: -16, next: 8, user: 'System' },
    { at: '22/08/2026 21:00', ingredient: 'Onion', type: 'WASTAGE', ref: 'WST-014', prev: 20, change: -2, next: 18, user: 'Arjun Das' },
    { at: '23/08/2026 11:00', ingredient: 'Onion', type: 'CONSUMPTION', ref: 'ORD-1035', prev: 18, change: -15, next: 3, user: 'System' },
    { at: '20/08/2026 09:00', ingredient: 'Egg', type: 'PURCHASE', ref: 'GRN-2026-0038', prev: 40, change: 120, next: 160, user: 'Sourav Roy' },
    { at: '22/08/2026 18:00', ingredient: 'Egg', type: 'CONSUMPTION', ref: 'ORD-1019', prev: 160, change: -160, next: 0, user: 'System' },
    { at: '23/08/2026 07:00', ingredient: 'Milk', type: 'ADJUSTMENT', ref: 'ADJ-006', prev: 8, change: -3, next: 5, user: 'Sourav Roy' },
];

export const WASTAGE = [
    { id: 'WST-014', ingredient: 'Onion', qty: 2, unit: 'KG', reason: 'Spoiled', cost: 64, employee: 'Arjun Das', date: '22/08/2026', notes: 'Found spoiled during morning check' },
    { id: 'WST-015', ingredient: 'Paneer', qty: 0.5, unit: 'KG', reason: 'Overcooked', cost: 160, employee: 'Chef Imran', date: '22/08/2026', notes: '' },
    { id: 'WST-016', ingredient: 'Tomato', qty: 1.2, unit: 'KG', reason: 'Preparation Waste', cost: 34, employee: 'Chef Imran', date: '23/08/2026', notes: 'Trimming waste' },
];

export const WASTAGE_REASONS = ['Expired', 'Damaged', 'Preparation Waste', 'Overcooked', 'Spillage', 'Other'];

export const STOCK_COUNTS = [
    { id: 'SC-2026-004', date: '20/08/2026', status: 'completed', by: 'Sourav Roy',
      lines: [
        { ingredient: 'Basmati Rice', system: 45, physical: 44, reason: '' },
        { ingredient: 'Onion', system: 20, physical: 17, reason: 'Spoilage not logged' },
        { ingredient: 'Coke', system: 50, physical: 50, reason: '' },
      ] },
];

export const TX_TYPES = ['OPENING', 'PURCHASE', 'CONSUMPTION', 'WASTAGE', 'ADJUSTMENT', 'RETURN', 'TRANSFER'];
