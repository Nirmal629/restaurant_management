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
