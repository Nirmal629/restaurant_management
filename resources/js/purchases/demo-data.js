export const VENUE = { name: 'Royal Bengal Restaurant', branch: 'Ichapur Main Branch' };
export const OPERATOR = { name: 'Sourav Roy', role: 'Inventory Manager' };

export const SUPPLIERS = [
    { id: 'SUP-001', name: 'Bengal Food Supplies', contact: 'Debashish Pal', phone: '9830011122', email: 'sales@bengalfood.example', gstin: '19AABCB1234K1Z1', address: 'Ichapur Industrial Area, North 24 Parganas', items: ['Basmati Rice', 'Cooking Oil', 'Paneer', 'Flour', 'Egg', 'Spices', 'Milk'], outstanding: 12400, status: 'active' },
    { id: 'SUP-002', name: 'Fresh Chicken Traders', contact: 'Manoj Halder', phone: '9830033344', email: 'orders@freshchicken.example', gstin: '19AABCF5678K1Z2', address: 'Barrackpore Trunk Road, Ichapur', items: ['Chicken', 'Mutton'], outstanding: 0, status: 'active' },
    { id: 'SUP-003', name: 'Kolkata Vegetable Market', contact: 'Ratan Das', phone: '9830055566', email: '', gstin: '', address: 'Sealdah Wholesale Market, Kolkata', items: ['Onion', 'Tomato'], outstanding: 3200, status: 'active' },
    { id: 'SUP-004', name: 'Eastern Beverage Distributors', contact: 'Sanjay Ghosh', phone: '9830077788', email: 'distro@easternbev.example', gstin: '19AABCE9012K1Z3', address: 'VIP Road, Kolkata', items: ['Coke'], outstanding: 0, status: 'inactive' },
];

export const PURCHASE_ORDERS = [
    {
        id: 'PO-2026-0084', supplier: 'Bengal Food Supplies', date: '23/08/2026', expectedDelivery: '25/08/2026', reference: 'Weekly restock', notes: '',
        status: 'approved', createdBy: 'Sourav Roy', approvedBy: 'Rakesh Singh',
        items: [
            { ingredient: 'Basmati Rice', currentStock: 42, qty: 50, unit: 'KG', rate: 95, tax: 5 },
            { ingredient: 'Cooking Oil', currentStock: 22, qty: 20, unit: 'LITRE', rate: 145, tax: 5 },
            { ingredient: 'Flour', currentStock: 30, qty: 25, unit: 'KG', rate: 42, tax: 5 },
        ],
        discount: 0, otherCharges: 200,
    },
    {
        id: 'PO-2026-0083', supplier: 'Fresh Chicken Traders', date: '22/08/2026', expectedDelivery: '23/08/2026', reference: '', notes: 'Urgent — chicken running low',
        status: 'partially_received', createdBy: 'Sourav Roy', approvedBy: 'Rakesh Singh',
        items: [{ ingredient: 'Chicken', currentStock: 8, qty: 30, unit: 'KG', rate: 210, tax: 0 }, { ingredient: 'Mutton', currentStock: 14, qty: 10, unit: 'KG', rate: 620, tax: 0 }],
        discount: 500, otherCharges: 0,
    },
    {
        id: 'PO-2026-0082', supplier: 'Kolkata Vegetable Market', date: '21/08/2026', expectedDelivery: '22/08/2026', reference: '', notes: '',
        status: 'received', createdBy: 'Sourav Roy', approvedBy: 'Rakesh Singh',
        items: [{ ingredient: 'Onion', currentStock: 3, qty: 30, unit: 'KG', rate: 32, tax: 0 }, { ingredient: 'Tomato', currentStock: 18, qty: 20, unit: 'KG', rate: 28, tax: 0 }],
        discount: 0, otherCharges: 100,
    },
    {
        id: 'PO-2026-0081', supplier: 'Eastern Beverage Distributors', date: '19/08/2026', expectedDelivery: '21/08/2026', reference: '', notes: '',
        status: 'ordered', createdBy: 'Sourav Roy', approvedBy: 'Rakesh Singh',
        items: [{ ingredient: 'Coke', currentStock: 48, qty: 96, unit: 'BOTTLE', rate: 38, tax: 12 }],
        discount: 0, otherCharges: 0,
    },
    {
        id: 'PO-2026-0080', supplier: 'Bengal Food Supplies', date: '18/08/2026', expectedDelivery: '20/08/2026', reference: '', notes: 'Draft — awaiting manager review',
        status: 'approval_pending', createdBy: 'Sourav Roy', approvedBy: null,
        items: [{ ingredient: 'Egg', currentStock: 0, qty: 240, unit: 'PCS', rate: 6, tax: 0 }],
        discount: 0, otherCharges: 0,
    },
    {
        id: 'PO-2026-0079', supplier: 'Kolkata Vegetable Market', date: '15/08/2026', expectedDelivery: '16/08/2026', reference: '', notes: 'Cancelled — supplier out of stock',
        status: 'cancelled', createdBy: 'Sourav Roy', approvedBy: null,
        items: [{ ingredient: 'Tomato', currentStock: 5, qty: 15, unit: 'KG', rate: 30, tax: 0 }],
        discount: 0, otherCharges: 0,
    },
];

export const GOODS_RECEIPTS = [
    {
        id: 'GRN-2026-0042', poRef: 'PO-2026-0083', supplier: 'Fresh Chicken Traders', invoiceNumber: 'INV-FCT-9981', receivedDate: '23/08/2026',
        items: [
            { ingredient: 'Chicken', ordered: 30, prevReceived: 0, receivedNow: 20, rejected: 0 },
            { ingredient: 'Mutton', ordered: 10, prevReceived: 0, receivedNow: 10, rejected: 0 },
        ],
    },
    {
        id: 'GRN-2026-0041', poRef: 'PO-2026-0082', supplier: 'Kolkata Vegetable Market', invoiceNumber: 'INV-KVM-3312', receivedDate: '22/08/2026',
        items: [
            { ingredient: 'Onion', ordered: 30, prevReceived: 0, receivedNow: 28, rejected: 2 },
            { ingredient: 'Tomato', ordered: 20, prevReceived: 0, receivedNow: 20, rejected: 0 },
        ],
    },
];

export const APPROVAL_REASONS = ['Budget approved', 'Urgent restock', 'Standard weekly order', 'Other'];
