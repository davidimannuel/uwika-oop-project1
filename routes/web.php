<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\TransactionAdminController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserAdminController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\EnsureAdmin;
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
  Route::get('/dashboard/incomes-this-year-group-by-month', [DashboardController::class, 'incomes_this_year_group_by_month'])->name('dashboard.incomes_this_year_group_by_month');
  Route::get('/dashboard/expenses-this-year-group-by-month', [DashboardController::class, 'expenses_this_year_group_by_month'])->name('dashboard.expenses_this_year_group_by_month');
  Route::get('/dashboard/total-incomes-and-expenses-all-time', [DashboardController::class, 'total_incomes_and_expenses_all_time'])->name('dashboard.total_incomes_and_expenses_all_time');
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
  // debt
  Route::get('/debt', [DebtController::class, 'index'])->name('debt.index');
  Route::get('/debt-total', [DebtController::class, 'total'])->name('debt.total');
  // admin middleware
  Route::middleware([EnsureAdmin::class])->group(function () {
    // admin transaction
    Route::get('/admin/users-transaction', [TransactionAdminController::class, 'index'])->name('transaction.admin.index');
    Route::get('/admin/users-transaction-total', [TransactionAdminController::class, 'total'])->name('transaction.admin.total');
    Route::get('/admin/users-transaction-csv', [TransactionAdminController::class, 'export_csv'])->name('transaction.admin.exportCsv');
    // user
    Route::get('/admin/user', [UserAdminController::class, 'index'])->name('user.admin.index');
    Route::get('/admin/list-user', [UserAdminController::class, 'listUser'])->name('user.admin.list');
    Route::patch('/admin/user/{user}/status', [UserAdminController::class, 'patch_status'])->name('user.admin.patch_status');
    Route::get('/admin/user/{user}/edit', [UserAdminController::class, 'edit'])->name('user.admin.edit');
    Route::put('/admin/user/{user}', [UserAdminController::class, 'update'])->name('user.admin.update');
  });
});