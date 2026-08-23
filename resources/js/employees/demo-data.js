export const VENUE = { name: 'Royal Bengal Restaurant', branch: 'Ichapur Main Branch' };

export const ROLES = ['Restaurant Owner', 'Restaurant Manager', 'Cashier', 'Waiter', 'Kitchen Manager', 'Chef', 'Inventory Manager'];
export const SHIFT_TYPES = { morning: { label: 'Morning', start: '09:00', end: '16:00' }, evening: { label: 'Evening', start: '16:00', end: '23:00' }, fullday: { label: 'Full Day', start: '09:00', end: '23:00' } };

export const MODULES = ['POS', 'Orders', 'Kitchen', 'Billing', 'Customers', 'Menu', 'Inventory', 'Purchases', 'Expenses', 'Reports', 'Employees', 'Settings'];
export const ACTIONS = ['View', 'Create', 'Edit', 'Cancel', 'Approve', 'Refund', 'Export'];

/** Role → default permission set (module: [actions]). Waiter/Chef etc. get a narrow slice; Owner gets everything. */
export const ROLE_DEFAULTS = {
    'Restaurant Owner': Object.fromEntries(MODULES.map((m) => [m, [...ACTIONS]])),
    'Restaurant Manager': Object.fromEntries(MODULES.map((m) => [m, ACTIONS.filter((a) => a !== 'Export' || true)])),
    'Cashier': { POS: ['View', 'Create'], Orders: ['View'], Billing: ['View', 'Create', 'Cancel'], Customers: ['View', 'Create'], Reports: ['View'] },
    'Waiter': { POS: ['View', 'Create'], Orders: ['View', 'Create'], Customers: ['View', 'Create'] },
    'Kitchen Manager': { Kitchen: ['View', 'Edit', 'Cancel'], Orders: ['View'], Inventory: ['View', 'Edit'] },
    'Chef': { Kitchen: ['View', 'Edit'] },
    'Inventory Manager': { Inventory: ['View', 'Create', 'Edit', 'Approve'], Purchases: ['View', 'Create', 'Edit', 'Approve'], Reports: ['View'] },
};

const emp = (o) => ({
    email: '', address: '', status: 'active', pinSet: true, shift: 'fullday', permissionOverrides: {},
    activity: [], ...o,
});

export const EMPLOYEES = [
    emp({ id: 'E1', employeeId: 'EMP-001', name: 'Rahul Das', role: 'Waiter', phone: '9800011001', email: 'rahul.das@royalbengal.example', joiningDate: '2023-04-12', shift: 'evening', activeTables: 4,
        activity: [{ at: '23/08/2026 19:12', text: 'Order created — ORD-1028' }, { at: '23/08/2026 08:05', text: 'Logged in' }],
        performance: { orders: 68, sales: 41200, avgBill: 606, tablesServed: 24 } }),
    emp({ id: 'E2', employeeId: 'EMP-002', name: 'Ankit Roy', role: 'Waiter', phone: '9800011002', joiningDate: '2023-06-01', shift: 'evening', activeTables: 3,
        activity: [{ at: '23/08/2026 19:05', text: 'Order created — ORD-1027' }],
        performance: { orders: 54, sales: 38900, avgBill: 720, tablesServed: 19 } }),
    emp({ id: 'E3', employeeId: 'EMP-003', name: 'Suman Ghosh', role: 'Waiter', phone: '9800011003', joiningDate: '2024-01-20', shift: 'morning', activeTables: 2,
        performance: { orders: 61, sales: 33450, avgBill: 548, tablesServed: 22 } }),
    emp({ id: 'E4', employeeId: 'EMP-004', name: 'Priya Sen', role: 'Waiter', phone: '9800011004', joiningDate: '2024-03-10', shift: 'morning', activeTables: 0,
        performance: { orders: 47, sales: 29800, avgBill: 634, tablesServed: 17 } }),
    emp({ id: 'E5', employeeId: 'EMP-005', name: 'Amit Sharma', role: 'Cashier', phone: '9800011005', joiningDate: '2022-11-05', shift: 'fullday', email: 'amit.sharma@royalbengal.example',
        activity: [{ at: '23/08/2026 20:52', text: 'Payment completed — INV-2026-001028' }, { at: '23/08/2026 08:00', text: 'Shift opened' }] }),
    emp({ id: 'E6', employeeId: 'EMP-006', name: 'Rakesh Singh', role: 'Restaurant Manager', phone: '9800011006', joiningDate: '2021-07-15', shift: 'fullday', email: 'rakesh.singh@royalbengal.example',
        activity: [{ at: '23/08/2026 11:00', text: 'Approved expense EXP-2026-0036' }] }),
    emp({ id: 'E7', employeeId: 'EMP-007', name: 'Arjun Das', role: 'Kitchen Manager', phone: '9800011007', joiningDate: '2022-02-18', shift: 'fullday',
        performance: { ordersPrepared: 412, avgPrepTime: 17 } }),
    emp({ id: 'E8', employeeId: 'EMP-008', name: 'Chef Imran', role: 'Chef', phone: '9800011008', joiningDate: '2021-09-01', shift: 'fullday',
        performance: { ordersPrepared: 380, avgPrepTime: 19 } }),
    emp({ id: 'E9', employeeId: 'EMP-009', name: 'Sourav Roy', role: 'Inventory Manager', phone: '9800011009', joiningDate: '2023-01-10', shift: 'morning', status: 'active' }),
    emp({ id: 'E10', employeeId: 'EMP-010', name: 'Nabila Khan', role: 'Waiter', phone: '9800011010', joiningDate: '2025-02-01', shift: 'evening', status: 'inactive', activeTables: 0,
        performance: { orders: 12, sales: 6200, avgBill: 517, tablesServed: 5 } }),
];
