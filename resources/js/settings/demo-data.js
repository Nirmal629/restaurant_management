export const SECTIONS = [
    { key: 'general', label: 'General' },
    { key: 'branch', label: 'Branch' },
    { key: 'tax', label: 'Tax & Billing' },
    { key: 'order', label: 'Order' },
    { key: 'kot', label: 'KOT & Kitchen' },
    { key: 'pos', label: 'POS' },
    { key: 'tables', label: 'Tables' },
    { key: 'payments', label: 'Payments' },
    { key: 'discounts', label: 'Discounts' },
    { key: 'inventory', label: 'Inventory' },
    { key: 'loyalty', label: 'Loyalty' },
    { key: 'reservations', label: 'Reservations' },
    { key: 'printing', label: 'Printing' },
    { key: 'notifications', label: 'Notifications' },
    { key: 'security', label: 'Security' },
    { key: 'numbering', label: 'Numbering' },
];

/** Defaults are internally consistent with the dummy data used across every other module. */
export const DEFAULT_SETTINGS = {
    general: { name: 'Royal Bengal Restaurant', phone: '+91 33 2593 4400', email: 'contact@royalbengal.example', address: '18 Grand Trunk Road, Ichapur, North 24 Parganas, WB 743144', currency: 'INR (₹)', timezone: 'Asia/Kolkata (IST)', dateFormat: 'DD/MM/YYYY', language: 'English' },
    branch: { name: 'Ichapur Main Branch', code: 'ICH-01', address: '18 Grand Trunk Road, Ichapur, North 24 Parganas, WB 743144', phone: '+91 33 2593 4400', openTime: '11:00', closeTime: '23:00' },
    tax: { gstEnabled: true, gstin: '19AABCR1234K1Z8', cgst: 2.5, sgst: 2.5, taxMode: 'exclusive', serviceCharge: 5, roundOff: true, invoicePrefix: 'INV', invoiceFormat: 'INV-{YYYY}-{000000}', receiptFooter: 'Thank you for dining with us — visit again!' },
    order: { prefix: 'ORD', defaultType: 'Dine In', autoConfirm: false, allowItemCancel: true, cancelReasonRequired: true, tableCloseBehavior: 'Cleaning' },
    kot: { prefix: 'KOT', stations: 'Main Kitchen, Tandoor, Chinese, Beverage, Dessert, Bar', autoPrint: true, warnMinutes: 15, criticalMinutes: 25, soundAlerts: true, kdsBehavior: 'Auto-refresh every 15s' },
    pos: { defaultFloor: 'Ground Floor', cardStyle: 'Compact', showImages: false, quickPayment: true, allowHold: true, barcodeBehavior: 'Scan appends to search', defaultCustomer: 'Walk-in Guest', shortcuts: true },
    tables: { numbering: 'T01, T02, … per floor', cleaningBehavior: 'Manual mark available', defaultGuestCount: 2, reservationHold: 15 },
    payments: { cash: true, upi: true, credit: true, debit: true, wallet: true, bank: true, mixedPayments: true, partialPayment: true, cashRounding: 'Nearest ₹1' },
    discounts: { cashierMax: 10, managerThreshold: 10, reasons: 'Customer loyalty, Service delay, Manager courtesy, Promotional offer, Corporate tie-up', complimentaryPermission: 'Manager approval required' },
    inventory: { stockTracking: true, allowNegative: false, lowStockAlerts: true, valuationMethod: 'Weighted Average (placeholder)', autoConsumption: true, wastageApproval: 'Kitchen Manager' },
    loyalty: { enabled: true, earnRule: '1 point per ₹10 spent', pointValue: 1, minRedeem: 50, maxRedeemPct: 50, expiryMonths: 12 },
    reservations: { enabled: true, slotDuration: 30, advanceBookingDays: 30, holdTime: 15, defaultDuration: 90, allowDeposits: true },
    printing: { receiptPrinter: 'Epson TM-T82 (Counter)', kitchenPrinter: 'Epson TM-T20 (Kitchen)', paperSize: '80mm', printLogo: true, printCustomer: true, printGst: true, autoPrint: true },
    notifications: { lowStock: true, reservationReminder: true, kitchenDelay: true, paymentFailure: true, approvalRequest: true },
    security: { sessionTimeout: 30, managerPinApproval: true, loginAttempts: 5, passwordPolicy: 'Minimum 8 characters, 1 number', auditLogging: true },
    numbering: { order: 'ORD-{0000}', kot: 'KOT-{0000}', invoice: 'INV-{YYYY}-{000000}', po: 'PO-{YYYY}-{0000}', grn: 'GRN-{YYYY}-{0000}', expense: 'EXP-{YYYY}-{0000}' },
};

export const FIELD_LABELS = {
    name: 'Restaurant Name', phone: 'Phone', email: 'Email', address: 'Address', currency: 'Currency', timezone: 'Timezone', dateFormat: 'Date Format', language: 'Language',
    code: 'Branch Code', openTime: 'Opening Time', closeTime: 'Closing Time',
    gstEnabled: 'GST Enabled', gstin: 'GSTIN', cgst: 'CGST %', sgst: 'SGST %', taxMode: 'Tax Mode', serviceCharge: 'Service Charge %', roundOff: 'Round Off', invoicePrefix: 'Invoice Prefix', invoiceFormat: 'Invoice Number Format', receiptFooter: 'Receipt Footer',
    prefix: 'Prefix', defaultType: 'Default Order Type', autoConfirm: 'Auto-Confirm Orders', allowItemCancel: 'Allow Item Cancellation', cancelReasonRequired: 'Cancellation Reason Required', tableCloseBehavior: 'Table Close Behaviour',
    stations: 'Kitchen Stations', autoPrint: 'Auto Print KOT', warnMinutes: 'Kitchen Warning Time (min)', criticalMinutes: 'Critical Delay Time (min)', soundAlerts: 'Sound Alerts', kdsBehavior: 'KDS Behaviour',
    defaultFloor: 'Default Floor', cardStyle: 'Product Card Style', showImages: 'Show Images', quickPayment: 'Quick Payment', allowHold: 'Allow Hold Order', barcodeBehavior: 'Barcode / SKU Behaviour', defaultCustomer: 'Default Customer', shortcuts: 'Keyboard Shortcuts',
    numbering: 'Table Numbering', cleaningBehavior: 'Cleaning Status Behaviour', defaultGuestCount: 'Default Guest Count', reservationHold: 'Reservation Hold Duration (min)',
    cash: 'Cash', upi: 'UPI', credit: 'Credit Card', debit: 'Debit Card', wallet: 'Wallet', bank: 'Bank Transfer', mixedPayments: 'Allow Mixed Payments', partialPayment: 'Allow Partial Payment', cashRounding: 'Cash Rounding',
    cashierMax: 'Maximum Cashier Discount %', managerThreshold: 'Manager Approval Threshold %', reasons: 'Discount Reasons', complimentaryPermission: 'Complimentary Permission',
    stockTracking: 'Stock Tracking', allowNegative: 'Allow Negative Stock', lowStockAlerts: 'Low Stock Alerts', valuationMethod: 'Default Valuation Method', autoConsumption: 'Auto Recipe Consumption', wastageApproval: 'Wastage Approval Role',
    enabled: 'Enabled', earnRule: 'Points Earning Rule', pointValue: 'Point Value (₹)', minRedeem: 'Minimum Redemption', maxRedeemPct: 'Maximum Redemption %', expiryMonths: 'Expiry (months)',
    slotDuration: 'Slot Duration (min)', advanceBookingDays: 'Advance Booking Days', holdTime: 'Reservation Hold Time (min)', defaultDuration: 'Default Duration (min)', allowDeposits: 'Allow Deposits',
    receiptPrinter: 'Receipt Printer', kitchenPrinter: 'Kitchen Printer', paperSize: 'Paper Size', printLogo: 'Print Logo', printCustomer: 'Print Customer', printGst: 'Print GST',
    lowStock: 'Low Stock', reservationReminder: 'Reservation Reminder', kitchenDelay: 'Kitchen Delay', paymentFailure: 'Payment Failure', approvalRequest: 'Approval Request',
    sessionTimeout: 'Session Timeout (min)', managerPinApproval: 'Manager PIN Approval', loginAttempts: 'Login Attempt Limit', passwordPolicy: 'Password Policy', auditLogging: 'Audit Logging',
    order: 'Order Prefix Format', kot: 'KOT Prefix Format', invoice: 'Invoice Prefix Format', po: 'Purchase Order Prefix Format', grn: 'GRN Prefix Format', expense: 'Expense Prefix Format',
};

export const NUMBERING_EXAMPLES = { order: 'ORD-1028', kot: 'KOT-1045', invoice: 'INV-2026-001028', po: 'PO-2026-0084', grn: 'GRN-2026-0042', expense: 'EXP-2026-0038' };
