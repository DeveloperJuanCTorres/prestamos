<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\TypeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Auth::routes();

Route::get('/', [App\Http\Controllers\HomeController::class, 'index']);

Route::get('/clients', [ClientController::class, 'index']);
Route::get('/clients/list', [ClientController::class, 'list'])->name('clients.list');
Route::post('/clients/store', [ClientController::class, 'store'])->name('clients.store');
Route::post('/clients/edit', [ClientController::class, 'edit'])->name('clients.edit');
Route::post('/clients/update', [ClientController::class, 'update'])->name('clients.update');
Route::post('/clients/delet', [ClientController::class, 'destroy'])->name('clients.delet');

Route::get('/types', [TypeController::class, 'index']);
Route::get('/types/list', [TypeController::class, 'list'])->name('types.list');
Route::post('/types/store', [TypeController::class, 'store'])->name('types.store');
Route::post('/types/edit', [TypeController::class, 'edit'])->name('types.edit');
Route::post('/types/update', [TypeController::class, 'update'])->name('types.update');
Route::post('/types/delet', [TypeController::class, 'destroy'])->name('types.delet');


Route::get('/prestamos', [LoanController::class, 'index']);
Route::get('/loans/list', [LoanController::class, 'list'])->name('loans.list');
Route::get('/loans/create', [LoanController::class, 'create'])->name('loans.create');
Route::post('/loans', [LoanController::class, 'store'])->name('loans.store');
Route::get('/loans/{loan}', [LoanController::class, 'show'])->name('loans.show');

Route::get('payments/{id}/pay', [LoanController::class, 'pay'])->name('payments.pay');
Route::get('loans/{id}/print-schedule', [LoanController::class, 'printSchedule'])->name('loans.printSchedule');
Route::get('payments/{id}/ticket', [LoanController::class, 'ticket'])->name('payments.ticket');

// Endpoint AJAX para límites
Route::get('/types/{id}/limits', [LoanController::class, 'typeLimits'])->name('types.limits');

Route::get('/calendar', [App\Http\Controllers\CalendarController::class, 'index']);


Route::group(['prefix' => 'admin'], function () {
    Voyager::routes();
});
