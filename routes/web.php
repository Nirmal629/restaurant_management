<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\EmployeeController;
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
    Route::view('/customers', 'customers')->name('customers');
    Route::view('/menu', 'menu')->name('menu');
    Route::view('/inventory', 'inventory')->name('inventory');
    Route::view('/purchases', 'purchases')->name('purchases');
    Route::view('/expenses', 'expenses')->name('expenses');
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
