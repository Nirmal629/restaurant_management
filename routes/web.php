<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PurchaseController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login')->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.attempt');
    Route::view('/forgot-password', 'auth.forgot-password')->name('password.request');
    Route::view('/reset-password', 'auth.reset-password')->name('password.reset');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::view('/change-password', 'auth.change-password')->name('password.change');
    Route::view('/dashboard', 'dashboard')->name('dashboard');
    Route::view('/pos', 'pos')->name('pos');
    Route::view('/tables', 'tables')->name('tables');
    Route::view('/kds', 'kds')->name('kds');
    Route::view('/billing', 'billing')->name('billing');
    Route::view('/reservations', 'reservations')->name('reservations');
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers');
    Route::get('/customers/data', [CustomerController::class, 'data'])->name('customers.data');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::patch('/customers/{customer}/vip', [CustomerController::class, 'vip'])->name('customers.vip');
    Route::patch('/customers/{customer}/loyalty', [CustomerController::class, 'loyalty'])->name('customers.loyalty');
    Route::patch('/customers/{customer}/note', [CustomerController::class, 'note'])->name('customers.note');
    Route::get('/menu', [MenuController::class, 'index'])->name('menu');
    Route::get('/menu/data', [MenuController::class, 'data'])->name('menu.data');
    Route::post('/menu/items', [MenuController::class, 'storeItem'])->name('menu.items.store');
    Route::put('/menu/items/{item}', [MenuController::class, 'updateItem'])->name('menu.items.update');
    Route::patch('/menu/items/{item}/availability', [MenuController::class, 'availability'])->name('menu.items.availability');
    Route::post('/menu/items/{item}/duplicate', [MenuController::class, 'duplicate'])->name('menu.items.duplicate');
    Route::post('/menu/categories', [MenuController::class, 'storeCategory'])->name('menu.categories.store');
    Route::put('/menu/categories/{category}', [MenuController::class, 'updateCategory'])->name('menu.categories.update');
    Route::post('/menu/modifiers', [MenuController::class, 'storeModifier'])->name('menu.modifiers.store');
    Route::put('/menu/modifiers/{group}', [MenuController::class, 'updateModifier'])->name('menu.modifiers.update');
    Route::post('/menu/combos', [MenuController::class, 'storeCombo'])->name('menu.combos.store');
    Route::put('/menu/combos/{combo}', [MenuController::class, 'updateCombo'])->name('menu.combos.update');
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory');
    Route::get('/inventory/data', [InventoryController::class, 'data'])->name('inventory.data');
    Route::post('/inventory/ingredients', [InventoryController::class, 'storeIngredient'])->name('inventory.ingredients.store');
    Route::put('/inventory/ingredients/{ingredient}', [InventoryController::class, 'updateIngredient'])->name('inventory.ingredients.update');
    Route::delete('/inventory/ingredients/{ingredient}', [InventoryController::class, 'destroyIngredient'])->name('inventory.ingredients.destroy');
    Route::patch('/inventory/ingredients/{ingredient}/adjust', [InventoryController::class, 'adjust'])->name('inventory.ingredients.adjust');
    Route::post('/inventory/suppliers', [InventoryController::class, 'storeSupplier'])->name('inventory.suppliers.store');
    Route::put('/inventory/suppliers/{supplier}', [InventoryController::class, 'updateSupplier'])->name('inventory.suppliers.update');
    Route::delete('/inventory/suppliers/{supplier}', [InventoryController::class, 'destroySupplier'])->name('inventory.suppliers.destroy');
    Route::post('/inventory/wastage', [InventoryController::class, 'storeWastage'])->name('inventory.wastage.store');
    Route::delete('/inventory/wastage/{wastage}', [InventoryController::class, 'destroyWastage'])->name('inventory.wastage.destroy');
    Route::post('/inventory/counts', [InventoryController::class, 'storeCount'])->name('inventory.counts.store');
    Route::delete('/inventory/counts/{count}', [InventoryController::class, 'destroyCount'])->name('inventory.counts.destroy');
    Route::put('/inventory/recipes/{menuItem}', [InventoryController::class, 'updateRecipe'])->name('inventory.recipes.update');
    Route::get('/inventory/export', [InventoryController::class, 'export'])->name('inventory.export');
    Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases');
    Route::get('/purchases/data', [PurchaseController::class, 'data'])->name('purchases.data');
    Route::post('/purchases/orders', [PurchaseController::class, 'storeOrder'])->name('purchases.orders.store');
    Route::put('/purchases/orders/{order}', [PurchaseController::class, 'updateOrder'])->name('purchases.orders.update');
    Route::patch('/purchases/orders/{order}/status', [PurchaseController::class, 'status'])->name('purchases.orders.status');
    Route::delete('/purchases/orders/{order}', [PurchaseController::class, 'destroyOrder'])->name('purchases.orders.destroy');
    Route::post('/purchases/receipts', [PurchaseController::class, 'storeReceipt'])->name('purchases.receipts.store');
    Route::delete('/purchases/receipts/{receipt}', [PurchaseController::class, 'destroyReceipt'])->name('purchases.receipts.destroy');
    Route::get('/purchases/export', [PurchaseController::class, 'export'])->name('purchases.export');
    Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses');
    Route::get('/expenses/data', [ExpenseController::class, 'data'])->name('expenses.data');
    Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::put('/expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
    Route::patch('/expenses/{expense}/status', [ExpenseController::class, 'status'])->name('expenses.status');
    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
    Route::get('/expenses/export', [ExpenseController::class, 'export'])->name('expenses.export');
    Route::view('/reports', 'reports')->name('reports');
    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees');
    Route::get('/employees/data', [EmployeeController::class, 'data'])->name('employees.data');
    Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
    Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::patch('/employees/{employee}/status', [EmployeeController::class, 'status'])->name('employees.status');
    Route::patch('/employees/{employee}/shift', [EmployeeController::class, 'shift'])->name('employees.shift');
    Route::patch('/employees/{employee}/permissions', [EmployeeController::class, 'permissions'])->name('employees.permissions');
    Route::view('/settings', 'settings')->name('settings');
});
