# Restaurant Management System Documentation

## 1. Project Overview

This project is a Laravel-based restaurant management system. It helps a restaurant manage the full daily workflow from table seating to order taking, kitchen preparation, billing, stock control, purchases, expenses, customers, employees, reports, and settings.

The system is built as a web application. Users log in, see only the modules they have permission to use, and perform actions through dashboard screens powered by Laravel controllers, Blade views, JavaScript stores, and database models.

## 2. Technology Stack

- Backend: Laravel 12 and PHP 8.2+
- Frontend: Blade templates, Alpine.js, Tailwind CSS, Vite
- Database: Laravel migrations and Eloquent models
- Authentication: Laravel session authentication
- Authorization: Custom permission middleware
- Testing: PHPUnit feature and unit tests

Important files:

- `routes/web.php`: All web routes for screens and API-style actions
- `app/Http/Controllers`: Module controllers
- `app/Models`: Database models and relationships
- `database/migrations`: Database structure
- `database/seeders`: Default data, roles, permissions, and demo records
- `resources/views`: Blade screens and reusable UI components
- `resources/js`: Frontend module logic
- `resources/css`: Module styling
- `tests/Feature`: Workflow tests for major modules

## 3. Authentication and Permissions

Users must log in before they can use the system.

Authentication routes:

- `/login`: Login page
- `/logout`: Logout
- `/change-password`: Change logged-in user's password
- `/forgot-password` and `/reset-password`: Password recovery views

The system uses a custom middleware named `permission`. It checks whether the logged-in user's employee role has access to a module and action.

Permission format:

- Module example: `POS`, `Billing`, `Inventory`
- Action example: `View`, `Create`, `Edit`, `Cancel`, `Approve`, `Refund`, `Export`

Example route permission:

```php
Route::get('/pos', [PosController::class, 'index'])
    ->middleware('permission:POS,View');
```

Default modules:

- POS
- Orders
- Kitchen
- Billing
- Customers
- Menu
- Inventory
- Purchases
- Expenses
- Reports
- Employees
- Settings

Default roles include Restaurant Owner, Restaurant Manager, Cashier, Waiter, Kitchen Manager, Chef, and Inventory Manager.

## 4. Main Modules

### Dashboard

Route:

- `/dashboard`

Purpose:

The dashboard is the first general screen after login. It gives users a central place to enter the system. The sidebar and permissions decide what each user can access.

Main files:

- `resources/views/dashboard.blade.php`
- `resources/views/components/shell/sidebar.blade.php`

### POS

Routes:

- `/pos`
- `/pos/data`
- `/pos/kot`
- `/pos/orders/{order}/billing`
- `/pos/items/{item}/status`
- `/pos/items/{item}/cancel`

Purpose:

The POS module is used by waiters and cashiers to create orders, add menu items, send KOTs to the kitchen, mark ready items as served, cancel items, and send finished orders to billing.

Main behavior:

- Shows menu categories and menu items
- Shows current and running orders
- Creates dine-in, takeaway, or delivery orders
- Sends order items to kitchen as KOTs
- Checks stock before sending KOT
- Consumes recipe stock when KOT items are sent
- Allows cancellation of items before they are served
- Sends orders to billing only after all items are served or cancelled

Main files:

- `app/Http/Controllers/PosController.php`
- `resources/views/pos.blade.php`
- `resources/js/pos.js`
- `resources/js/pos/store.js`
- `resources/views/components/pos/*`

### Orders

Routes:

- `/orders`
- `/orders/data`
- `/orders/{order}/status`
- `/orders/items/{item}/status`

Purpose:

The Orders module tracks restaurant orders after they are created. It helps staff see order status, item status, kitchen progress, and billing progress.

Main behavior:

- Lists open and active orders
- Updates order status
- Updates individual item status
- Connects POS, kitchen, billing, and tables

Main files:

- `app/Http/Controllers/OrderController.php`
- `resources/views/orders.blade.php`
- `resources/js/orders.js`
- `resources/js/orders/store.js`

### Tables

Routes:

- `/tables`
- `/tables/data`
- `/tables`
- `/tables/{table}`
- `/tables/{table}/status`
- `/tables/{table}/start`
- `/tables/{table}/reserve`
- `/tables/floors`

Purpose:

The Tables module manages the restaurant floor map. Staff can see table availability, reserve tables, start orders, add tables, edit tables, and add floors.

Table statuses include:

- `available`
- `reserved`
- `occupied`
- `billing`
- `cleaning`
- `disabled`

Main behavior:

- Displays floors and tables
- Starts a new dine-in order from a table
- Creates a walk-in reservation for a table
- Marks tables as available, cleaning, or disabled
- Prevents a table from being marked available when an active order still exists
- Updates reservations and POS through realtime notifications

Main files:

- `app/Http/Controllers/TableController.php`
- `resources/views/tables.blade.php`
- `resources/js/tables.js`
- `resources/js/tables/store.js`
- `resources/views/components/tables/*`

### Reservations

Routes:

- `/reservations`
- `/reservations/data`
- `/reservations`
- `/reservations/{reservation}`
- `/reservations/{reservation}/status`
- `/reservations/{reservation}/seat`

Purpose:

The Reservations module manages booking requests and seating guests.

Main behavior:

- Creates reservations with customer name, phone, date, time, guest count, table, occasion, source, and deposit
- Updates reservation details
- Changes reservation status to confirmed, arrived, cancelled, or no-show
- Seats a reservation and creates a dine-in order
- Marks the selected table as reserved or occupied
- Keeps reservation activity history

Main files:

- `app/Http/Controllers/ReservationController.php`
- `resources/views/reservations.blade.php`
- `resources/js/reservations.js`
- `resources/js/reservations/store.js`
- `resources/views/components/reservations/*`

### Kitchen Display System

Routes:

- `/kds`
- `/kds/data`
- `/kds/orders/{order}/status`
- `/kds/items/{item}/status`

Purpose:

The KDS module is used by kitchen staff to view KOT tickets and update food preparation status.

Main behavior:

- Shows kitchen tickets
- Groups or filters work by kitchen station
- Updates order and item preparation status
- Sends realtime updates to POS, Orders, and Billing

Main files:

- `app/Http/Controllers/KdsController.php`
- `resources/views/kds.blade.php`
- `resources/js/kds.js`
- `resources/js/kds/store.js`
- `resources/views/components/kds/*`

### Billing

Routes:

- `/billing`
- `/billing/data`
- `/billing/invoices/{invoice}/discount`
- `/billing/invoices/{invoice}/adjustments`
- `/billing/invoices/{invoice}/coupon`
- `/billing/items/{item}`
- `/billing/invoices/{invoice}/payments`
- `/billing/invoices/{invoice}/complete`
- `/billing/invoices/{invoice}/refunds`
- `/billing/invoices/{invoice}/void`
- `/billing/invoices/{invoice}/close`

Purpose:

The Billing module creates and settles invoices for completed orders.

Main behavior:

- Creates an invoice when an order enters billing
- Shows bill items, customer details, taxes, service charge, discounts, coupons, payments, refunds, and due amount
- Applies bill-level discounts
- Applies item-level discounts
- Marks items as complimentary
- Cancels bill items
- Applies and removes coupons
- Adds customer or GST invoice details
- Accepts payments by cash, UPI, card, wallet, bank, or other methods
- Completes payment only when the due amount is zero
- Handles full, partial, and item refunds
- Voids invoices
- Closes a table and moves it to cleaning

Important billing calculations are handled on the `Invoice` model:

- Subtotal
- Item discount total
- Complimentary total
- Bill discount amount
- Taxable amount
- CGST and SGST
- Service charge
- Grand total
- Round off
- Paid amount
- Refunded amount
- Due amount
- Invoice status

Main files:

- `app/Http/Controllers/BillingController.php`
- `app/Models/Invoice.php`
- `resources/views/billing.blade.php`
- `resources/js/billing.js`
- `resources/js/billing/store.js`
- `resources/views/components/billing/*`

### Customers and Coupons

Routes:

- `/customers`
- `/customers/data`
- `/customers`
- `/customers/{customer}`
- `/customers/{customer}/vip`
- `/customers/{customer}/loyalty`
- `/customers/{customer}/note`
- `/customers/coupons`
- `/customers/coupons/{coupon}`

Purpose:

The Customers module stores guest profiles and coupon offers.

Main behavior:

- Creates and updates customers
- Tracks phone, email, GSTIN, business name, address, VIP status, notes, and loyalty points
- Marks customers as VIP
- Updates loyalty balance
- Saves customer notes
- Creates, edits, and deletes coupons
- Coupons can be percentage or amount based
- Coupons can have minimum bill amount, maximum discount, expiry date, usage limit, and walk-in rules
- Billing validates coupons before applying them

Main files:

- `app/Http/Controllers/CustomerController.php`
- `app/Models/Customer.php`
- `app/Models/Coupon.php`
- `app/Models/CouponRedemption.php`
- `resources/views/customers.blade.php`
- `resources/js/customers.js`
- `resources/js/customers/store.js`

### Menu

Routes:

- `/menu`
- `/menu/data`
- `/menu/items`
- `/menu/items/{item}`
- `/menu/items/{item}/availability`
- `/menu/items/{item}/duplicate`
- `/menu/categories`
- `/menu/categories/{category}`
- `/menu/modifiers`
- `/menu/modifiers/{group}`
- `/menu/combos`
- `/menu/combos/{combo}`

Purpose:

The Menu module manages what can be sold in POS.

Main behavior:

- Creates and updates menu items
- Changes item availability
- Duplicates existing items
- Creates and updates categories
- Creates and updates modifier groups and modifier options
- Creates and updates combos
- Connects menu items to kitchen stations
- Supports stock-tracked items through recipes

Main files:

- `app/Http/Controllers/MenuController.php`
- `app/Models/MenuItem.php`
- `app/Models/MenuCategory.php`
- `app/Models/MenuItemVariant.php`
- `app/Models/ModifierGroup.php`
- `app/Models/ModifierOption.php`
- `app/Models/Combo.php`
- `app/Models/ComboItem.php`
- `resources/views/menu.blade.php`
- `resources/js/menu.js`
- `resources/js/menu/store.js`

### Inventory

Routes:

- `/inventory`
- `/inventory/data`
- `/inventory/ingredients`
- `/inventory/ingredients/{ingredient}`
- `/inventory/ingredients/{ingredient}/adjust`
- `/inventory/suppliers`
- `/inventory/suppliers/{supplier}`
- `/inventory/wastage`
- `/inventory/wastage/{wastage}`
- `/inventory/counts`
- `/inventory/counts/{count}`
- `/inventory/recipes/{menuItem}`
- `/inventory/export`

Purpose:

The Inventory module controls stock, suppliers, wastage, stock counts, and menu recipes.

Main behavior:

- Creates and updates ingredients
- Tracks current stock, minimum stock, reorder level, average cost, supplier, and storage location
- Records opening stock and stock changes in a ledger
- Manually adjusts stock
- Deletes ingredients
- Creates and manages suppliers
- Records wastage and reduces stock
- Performs stock counts and applies variances
- Defines recipes for stock-tracked menu items
- Exports inventory as CSV

Supported units:

- KG
- GRAM
- LITRE
- ML
- PCS
- PACK
- BOX
- BOTTLE

Stock transaction types:

- OPENING
- PURCHASE
- CONSUMPTION
- WASTAGE
- ADJUSTMENT
- RETURN
- TRANSFER

Main files:

- `app/Http/Controllers/InventoryController.php`
- `app/Models/Ingredient.php`
- `app/Models/StockLedgerEntry.php`
- `app/Models/Wastage.php`
- `app/Models/StockCount.php`
- `app/Models/StockCountLine.php`
- `app/Models/Recipe.php`
- `app/Models/RecipeLine.php`
- `resources/views/inventory.blade.php`
- `resources/js/inventory.js`
- `resources/js/inventory/store.js`

### Purchases

Routes:

- `/purchases`
- `/purchases/data`
- `/purchases/orders`
- `/purchases/orders/{order}`
- `/purchases/orders/{order}/status`
- `/purchases/receipts`
- `/purchases/receipts/{receipt}`
- `/purchases/export`

Purpose:

The Purchases module manages supplier purchase orders and goods receipt notes.

Main behavior:

- Creates purchase orders for ingredients
- Saves supplier, expected delivery date, reference, notes, discounts, and other charges
- Tracks ordered quantity, unit, rate, and tax
- Updates purchase orders before they are received or cancelled
- Moves purchase orders through approval and order statuses
- Records goods receipts
- Adds accepted stock into inventory
- Creates stock ledger entries for received stock
- Marks purchase orders as partially received or received
- Deletes receipts and reverses accepted stock
- Exports purchases as CSV

Main files:

- `app/Http/Controllers/PurchaseController.php`
- `app/Models/PurchaseOrder.php`
- `app/Models/PurchaseOrderLine.php`
- `app/Models/GoodsReceipt.php`
- `app/Models/GoodsReceiptLine.php`
- `resources/views/purchases.blade.php`
- `resources/js/purchases.js`
- `resources/js/purchases/store.js`

### Expenses

Routes:

- `/expenses`
- `/expenses/data`
- `/expenses`
- `/expenses/{expense}`
- `/expenses/{expense}/status`
- `/expenses/export`

Purpose:

The Expenses module tracks restaurant spending.

Main behavior:

- Creates expenses
- Updates expense details
- Changes expense status
- Deletes or cancels expenses
- Keeps expense activity history
- Exports expenses
- High-value expenses can be handled through draft or approval status depending on controller rules

Main files:

- `app/Http/Controllers/ExpenseController.php`
- `app/Models/Expense.php`
- `app/Models/ExpenseActivity.php`
- `resources/views/expenses.blade.php`
- `resources/js/expenses.js`
- `resources/js/expenses/store.js`

### Reports

Routes:

- `/reports`
- `/reports/data`
- `/reports/export/{kind}`

Purpose:

The Reports module summarizes restaurant performance.

Main report groups:

- Sales
- Menu
- Kitchen
- Inventory
- Purchase
- Customer
- Financial
- Employee

Examples of available reports:

- Daily Sales
- Sales by Date
- Sales by Hour
- Sales by Order Type
- Sales by Payment Method
- Sales by Waiter
- Sales by Table
- Discount Report
- Cancellation/Void Report
- Tax Report
- Item Sales
- Category Sales
- Top Selling Items
- Least Selling Items
- Menu Profitability
- Current Stock
- Low Stock
- Inventory Valuation
- Stock Movement
- Wastage
- Purchase Summary
- Supplier Purchases
- Top Customers
- Expenses
- Refunds

Exports:

The route accepts `csv`, `excel`, or `pdf` as export kinds, but the current implementation returns CSV content for all accepted kinds.

Main files:

- `app/Http/Controllers/ReportController.php`
- `resources/views/reports.blade.php`
- `resources/js/reports.js`
- `resources/js/reports/store.js`

### Employees

Routes:

- `/employees`
- `/employees/data`
- `/employees`
- `/employees/{employee}`
- `/employees/{employee}/status`
- `/employees/{employee}/shift`
- `/employees/{employee}/permissions`

Purpose:

The Employees module manages staff records, login users, roles, shifts, and permissions.

Main behavior:

- Creates employees
- Creates a linked login user for new employees
- Updates employee details
- Activates or deactivates employees
- Changes shifts
- Updates employee permissions
- Uses role permissions as the base permission set

Main files:

- `app/Http/Controllers/EmployeeController.php`
- `app/Models/Employee.php`
- `app/Models/User.php`
- `app/Models/Role.php`
- `app/Models/Permission.php`
- `resources/views/employees.blade.php`
- `resources/js/employees.js`
- `resources/js/employees/store.js`

### Settings

Routes:

- `/settings`
- `/settings/data`
- `/settings/{section}`

Purpose:

The Settings module stores configurable application settings by section.

Main behavior:

- Loads saved settings
- Saves settings for a section
- Resets a settings section

Main files:

- `app/Http/Controllers/SettingController.php`
- `app/Models/AppSetting.php`
- `resources/views/settings.blade.php`
- `resources/js/settings.js`
- `resources/js/settings/store.js`

## 5. Realtime Updates

The app has a simple realtime notification system.

Main files:

- `app/Services/RealtimeNotifier.php`
- `app/Http/Controllers/RealtimeController.php`
- `resources/js/shared/realtime.js`

How it works:

- Backend actions call `RealtimeNotifier->touch(...)` with one or more topic names.
- The frontend listens to `/realtime/stream`.
- When a topic changes, related screens can refresh their data.

Common topics:

- `pos`
- `orders`
- `kitchen`
- `tables`
- `billing`
- `reservations`
- `inventory`
- `menu`

## 6. Stock Consumption

Stock consumption is handled by `app/Services/StockConsumptionService.php`.

Purpose:

It connects POS, recipes, and inventory.

Main behavior:

- Before a KOT is sent, the system checks whether recipe ingredients are available.
- When an item is sent to the kitchen, the system consumes the required ingredients.
- When an item is cancelled or refunded, consumed stock can be reversed.
- Inventory and menu screens receive realtime updates after stock changes.

This prevents staff from selling stock-tracked menu items when ingredients are unavailable.

## 7. Database Areas

The database is split into practical restaurant areas.

Core user and access tables:

- `users`
- `employees`
- `roles`
- `permissions`
- `role_permission`
- `branches`

Menu tables:

- `menu_categories`
- `kitchen_stations`
- `menu_items`
- `menu_item_variants`
- `modifier_groups`
- `modifier_options`
- `menu_item_modifier_group`
- `combos`
- `combo_items`

Floor and table tables:

- `floors`
- `restaurant_tables`

Customer and reservation tables:

- `customers`
- `reservations`
- `reservation_activities`

Order and kitchen tables:

- `orders`
- `order_items`
- `kots`

Billing tables:

- `invoices`
- `payments`
- `refunds`
- `coupons`
- `coupon_redemptions`

Inventory tables:

- `ingredients`
- `stock_ledger_entries`
- `wastages`
- `stock_counts`
- `stock_count_lines`
- `recipes`
- `recipe_lines`

Purchase tables:

- `suppliers`
- `purchase_orders`
- `purchase_order_lines`
- `goods_receipts`
- `goods_receipt_lines`

Expense and settings tables:

- `expenses`
- `expense_activities`
- `app_settings`

## 8. Main Business Workflows

### Dine-in order workflow

1. Staff opens Tables.
2. Staff selects an available table and starts an order.
3. The table becomes occupied.
4. Staff opens POS and adds menu items.
5. Staff sends items to KOT.
6. Stock is checked and consumed.
7. Kitchen prepares items in KDS.
8. Items are marked ready.
9. POS marks ready items as served.
10. Staff sends the order to Billing.
11. Billing creates an invoice.
12. Cashier applies discounts, coupons, customer details, and payments.
13. Invoice is completed.
14. Table is closed and moved to cleaning.
15. Table can later be marked available.

### Reservation workflow

1. Staff creates a reservation.
2. If a table is assigned, the table becomes reserved.
3. Staff updates the reservation as confirmed or arrived.
4. When guests arrive, staff seats the reservation.
5. The system creates an order and marks the table occupied.
6. The workflow continues through POS, KDS, and Billing.

### Purchase and inventory workflow

1. Inventory team creates ingredients and suppliers.
2. Purchase team creates a purchase order.
3. Manager approves or updates the purchase order status.
4. Goods are received using a goods receipt.
5. Accepted quantities increase ingredient stock.
6. Stock ledger records the purchase.
7. Inventory reports show the updated stock value.

### Stock-tracked menu item workflow

1. Inventory team creates ingredient records.
2. Inventory team creates a recipe for a menu item.
3. POS checks recipe stock before sending KOT.
4. If stock is enough, KOT is created and stock is consumed.
5. If stock is not enough, POS blocks the action.
6. Cancelled or refunded items can reverse stock.

### Billing and refund workflow

1. Order reaches billing only after all kitchen items are served or cancelled.
2. Billing creates or loads the invoice.
3. Cashier can apply item discounts, bill discounts, coupons, loyalty, and GST details.
4. Cashier records one or more payments.
5. Invoice can be completed when no amount is due.
6. Refunds can be full, partial, or item-based.
7. Item refunds can reverse stock.
8. Invoice can be voided when needed.

## 9. Frontend Structure

Each main screen usually has:

- A Blade page in `resources/views`
- A JavaScript entry file in `resources/js`
- A module store in `resources/js/{module}/store.js`
- Optional demo data in `resources/js/{module}/demo-data.js`
- Reusable Blade components in `resources/views/components/{module}`
- Optional CSS in `resources/css`

Examples:

- POS: `pos.blade.php`, `pos.js`, `pos/store.js`, POS components
- Billing: `billing.blade.php`, `billing.js`, `billing/store.js`, Billing components
- Tables: `tables.blade.php`, `tables.js`, `tables/store.js`, Tables components

## 10. Seed Data

Seeders create the base restaurant data needed for testing and development.

Main seeders:

- `BranchSeeder`
- `RoleAndPermissionSeeder`
- `EmployeeSeeder`
- `CustomerSeeder`
- `CouponSeeder`
- `MenuSeeder`
- `FloorAndTableSeeder`
- `InventorySeeder`
- `SupplierSeeder`
- `PurchaseSeeder`
- `ReservationSeeder`
- `OrderAndBillingSeeder`
- `ExpenseSeeder`

The `DatabaseSeeder` runs these seeders in order.

## 11. Tests

The project includes feature tests for major workflows.

Covered areas:

- Authentication and permissions
- Employee creation and password change
- Table and reservation lifecycle
- POS and stock consumption
- Kitchen display workflow
- Order status flow
- Billing, payments, refunds, voids, and billing queue
- Customer coupon workflow
- Menu item update
- Inventory supplier workflow
- Purchase order and goods receipt workflow
- Expense workflow
- Reports and settings

Run tests:

```bash
composer test
```

Or:

```bash
php artisan test
```

## 12. Local Setup

Install PHP dependencies:

```bash
composer install
```

Create environment file:

```bash
copy .env.example .env
```

Generate app key:

```bash
php artisan key:generate
```

Run database migrations:

```bash
php artisan migrate
```

Seed database:

```bash
php artisan db:seed
```

Install frontend dependencies:

```bash
npm install
```

Build frontend assets:

```bash
npm run build
```

Start development tools:

```bash
composer run dev
```

This starts the Laravel server, queue listener, log viewer, and Vite dev server together.

## 13. Notes for Developers

- Add new web routes in `routes/web.php`.
- Protect new screens and actions with the permission middleware.
- Put module business logic in the matching controller or a service when shared by several controllers.
- Put database relationships and calculations in models.
- Use `RealtimeNotifier` when a change should refresh other screens.
- Add migrations for database changes.
- Add seed data when a feature needs default records.
- Add feature tests for complete workflows, especially when changing POS, stock, billing, or permissions.

## 14. Current Limitations and Observations

- The default `README.md` is still the standard Laravel README. This project documentation file explains the actual restaurant system.
- Report export accepts CSV, Excel, and PDF route values, but currently returns CSV content.
- Some frontend files include demo-data modules. These are useful for UI fallback or development but the main controllers provide real database-backed data.
- Realtime behavior is implemented with a lightweight stream and topic versioning, not a large external realtime service.

