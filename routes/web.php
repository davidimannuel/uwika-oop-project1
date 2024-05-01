<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // return view('welcome');
    return view('home');
})->name('home');

// login
Route::get('/login', [LoginController::class, 'index'])->name('login')->middleware('guest');
Route::post('/login', [LoginController::class, 'authenticate'])->name('login.authenticate');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// register
Route::get('/register', [RegisterController::class, 'index'])->name('register')->middleware('guest');
Route::post('/register', [RegisterController::class, 'store'])->name('register.store')->middleware('guest');

// protected by auth middleware
Route::middleware(['auth'])->group(function () {
  // account
  Route::resource('account', AccountController::class);
  Route::get('/list-account', [AccountController::class, 'listAccount'])->name('account.list');
  // category
  Route::resource('category', CategoryController::class);
  Route::get('/list-category', [CategoryController::class, 'listCategory'])->name('category.list');
  // transaction
  Route::resource('transaction', TransactionController::class);
});