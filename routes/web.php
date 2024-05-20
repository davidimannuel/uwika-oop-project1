<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard.index');
    }
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
  // Dashboard
  Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
  Route::get('/dashboard/incomes-this-year-group-by-month', [DashboardController::class, 'incomes_this_year_group_by_month'])->name('dashboard.incomesThisYearGroupByMonth');
  Route::get('/dashboard/expenses-this-year-group-by-month', [DashboardController::class, 'expenses_this_year_group_by_month'])->name('dashboard.expensesThisYearGroupByMonth');
  // account
  Route::resource('account', AccountController::class);
  Route::get('/list-account', [AccountController::class, 'listAccount'])->name('account.list');
  // category
  Route::resource('category', CategoryController::class);
  Route::get('/list-category', [CategoryController::class, 'listCategory'])->name('category.list');
  // transaction
  Route::resource('transaction', TransactionController::class);
  Route::get('/transaction-total', [TransactionController::class, 'total'])->name('transaction.total');
  Route::get('/transaction-csv', [TransactionController::class, 'export_csv'])->name('transaction.exportCsv');
});