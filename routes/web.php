<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'auth.login')->name('home');
Route::view('/login', 'auth.login')->name('login');
Route::view('/forgot-password', 'auth.forgot-password')->name('password.request');
Route::view('/reset-password', 'auth.reset-password')->name('password.reset');
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
Route::view('/employees', 'employees')->name('employees');
Route::view('/settings', 'settings')->name('settings');
