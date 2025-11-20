<?php

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
Route::get('/clients', [App\Http\Controllers\ClientController::class, 'index']);
Route::get('/prestamos', [App\Http\Controllers\PrestamoController::class, 'index']);
Route::get('/types', [App\Http\Controllers\TypeController::class, 'index']);
Route::get('/calendar', [App\Http\Controllers\CalendarController::class, 'index']);


Route::group(['prefix' => 'admin'], function () {
    Voyager::routes();
});
