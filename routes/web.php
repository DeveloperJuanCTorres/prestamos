<?php

use App\Http\Controllers\CalendarController;
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
Route::get('/clients/search', function (Illuminate\Http\Request $request) {
    $q = $request->q;

    return \App\Models\Client::where('name', 'like', "%$q%")
        ->orWhere('numero_doc', 'like', "%$q%")
        ->orderBy('name')
        ->limit(30)
        ->get(['id', 'name', 'numero_doc']);
});

Route::get('/types', [TypeController::class, 'index']);
Route::get('/types/list', [TypeController::class, 'list'])->name('types.list');
Route::post('/types/store', [TypeController::class, 'store'])->name('types.store');
Route::post('/types/edit', [TypeController::class, 'edit'])->name('types.edit');
Route::post('/types/update', [TypeController::class, 'update'])->name('types.update');
Route::post('/types/delet', [TypeController::class, 'destroy'])->name('types.delet');


Route::get('/prestamos', [LoanController::class, 'index'])->name('loans.index');
Route::get('/loans/list', [LoanController::class, 'list'])->name('loans.list');
Route::get('/loans/create', [LoanController::class, 'create'])->name('loans.create');
Route::post('/loans', [LoanController::class, 'store'])->name('loans.store');
Route::get('/loans/{loan}', [LoanController::class, 'show'])->name('loans.show');

Route::post('payments/{id}/pay', [LoanController::class, 'pay'])->name('payments.pay');
Route::get('loans/{id}/print-schedule', [LoanController::class, 'printSchedule'])->name('loans.printSchedule');
Route::get('payments/{id}/ticket', [LoanController::class, 'ticket'])->name('payments.ticket');
Route::get('payments/{id}/ticket-data', [LoanController::class, 'ticketData'])->name('payments.ticket.data');

Route::get('loans/{loan}/edit', [LoanController::class, 'edit'])->name('loans.edit');
Route::put('loans/{loan}', [LoanController::class, 'update'])->name('loans.update');


Route::get('/reporte-general', [LoanController::class, 'reporteGeneral'])
    ->name('reporte.general');

Route::get('/reporte-clientes', [LoanController::class, 'reporteClientes'])
->name('reporte.clientes');

Route::get('/reporte-prestamos', [LoanController::class, 'reportePrestamos'])
    ->name('reporte.prestamos');

Route::get('/reporte-pagos', [LoanController::class, 'reportePagos'])
->name('reporte.pagos');

// Endpoint AJAX para límites
Route::get('/types/{id}/limits', [LoanController::class, 'typeLimits'])->name('types.limits');

Route::get('/calendar', [CalendarController::class, 'index']);
Route::get('/events', [CalendarController::class, 'load']);
Route::post('/events', [CalendarController::class, 'store']);
Route::put('/events/{id}', [CalendarController::class, 'update']);
Route::delete('/events/{id}', [CalendarController::class, 'destroy']);


Route::group(['prefix' => 'admin'], function () {
    Voyager::routes();
});
