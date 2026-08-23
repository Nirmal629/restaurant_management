export const VENUE = { name: 'Royal Bengal Restaurant', branch: 'Ichapur Main Branch' };
export const OPERATOR = { name: 'Rakesh Singh', role: 'Restaurant Manager' };

export const CATEGORIES = ['Rent', 'Electricity', 'Gas', 'Water', 'Salary', 'Maintenance', 'Cleaning', 'Marketing', 'Transport', 'Internet', 'Packaging', 'Repair', 'Miscellaneous'];
export const PAYMENT_METHODS = ['Cash', 'UPI', 'Card', 'Bank Transfer', 'Other'];
export const APPROVAL_THRESHOLD = 10000;

export const EXPENSES = [
    { id: 'EXP-2026-0038', date: '23/08/2026', category: 'Gas', description: 'LPG commercial cylinder refill ×4', vendor: 'Ichapur Gas Agency', method: 'Cash', amount: 6400, employee: 'Amit Sharma', status: 'paid', receipt: true, branch: 'Ichapur Main Branch' },
    { id: 'EXP-2026-0037', date: '23/08/2026', category: 'Cleaning', description: 'Weekly deep-cleaning service', vendor: 'Sparkle Facility Services', method: 'UPI', amount: 3200, employee: 'Rakesh Singh', status: 'paid', receipt: true, branch: 'Ichapur Main Branch' },
    { id: 'EXP-2026-0036', date: '22/08/2026', category: 'Electricity', description: 'August electricity bill', vendor: 'WBSEDCL', method: 'Bank Transfer', amount: 18450, employee: 'Rakesh Singh', status: 'approved', receipt: true, branch: 'Ichapur Main Branch' },
    { id: 'EXP-2026-0035', date: '22/08/2026', category: 'Repair', description: 'Walk-in freezer compressor repair', vendor: 'CoolTech Services', method: 'Cash', amount: 4500, employee: 'Sourav Roy', status: 'paid', receipt: false, branch: 'Ichapur Main Branch' },
    { id: 'EXP-2026-0034', date: '21/08/2026', category: 'Marketing', description: 'Instagram ad campaign — August', vendor: 'Meta Ads', method: 'Card', amount: 5000, employee: 'Rakesh Singh', status: 'paid', receipt: true, branch: 'Ichapur Main Branch' },
    { id: 'EXP-2026-0033', date: '20/08/2026', category: 'Packaging', description: 'Takeaway containers — 500 units', vendor: 'EcoPack Supplies', method: 'UPI', amount: 6200, employee: 'Amit Sharma', status: 'paid', receipt: true, branch: 'Ichapur Main Branch' },
    { id: 'EXP-2026-0032', date: '19/08/2026', category: 'Transport', description: 'Vegetable delivery fuel reimbursement', vendor: '', method: 'Cash', amount: 800, employee: 'Sourav Roy', status: 'paid', receipt: false, branch: 'Ichapur Main Branch' },
    { id: 'EXP-2026-0031', date: '18/08/2026', category: 'Internet', description: 'Broadband + POS network — August', vendor: 'Airtel Business', method: 'Bank Transfer', amount: 2499, employee: 'Rakesh Singh', status: 'paid', receipt: true, branch: 'Ichapur Main Branch' },
    { id: 'EXP-2026-0030', date: '17/08/2026', category: 'Rent', description: 'Shop rent — August', vendor: 'Property Owner', method: 'Bank Transfer', amount: 65000, employee: 'Rakesh Singh', status: 'approved', receipt: false, branch: 'Ichapur Main Branch' },
    { id: 'EXP-2026-0029', date: '16/08/2026', category: 'Maintenance', description: 'AC servicing — 3 units', vendor: 'CoolTech Services', method: 'Cash', amount: 3600, employee: 'Sourav Roy', status: 'draft', receipt: false, branch: 'Ichapur Main Branch' },
    { id: 'EXP-2026-0028', date: '15/08/2026', category: 'Miscellaneous', description: 'Independence Day decoration', vendor: 'Local vendor', method: 'Cash', amount: 1200, employee: 'Amit Sharma', status: 'rejected', receipt: false, branch: 'Ichapur Main Branch', rejectReason: 'No prior approval taken' },
    { id: 'EXP-2026-0027', date: '10/08/2026', category: 'Salary', description: 'Staff advance — kitchen team', vendor: '', method: 'Cash', amount: 15000, employee: 'Rakesh Singh', status: 'approved', receipt: false, branch: 'Ichapur Main Branch' },
];

export const STATUS_TIMELINE = {
    'EXP-2026-0036': [
        { at: '22/08/2026 09:10', text: 'Expense created by Rakesh Singh' },
        { at: '22/08/2026 09:15', text: 'Submitted for approval — exceeds ₹10,000 threshold' },
        { at: '22/08/2026 11:00', text: 'Approved by Aisha Rahman (Owner)' },
    ],
    'EXP-2026-0028': [
        { at: '15/08/2026 18:00', text: 'Expense created by Amit Sharma' },
        { at: '16/08/2026 09:00', text: 'Rejected by Rakesh Singh — no prior approval taken' },
    ],
};
