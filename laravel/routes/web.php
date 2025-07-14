<?php

use App\Http\Controllers\MyController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MyDcaKeyController;
use App\Http\Controllers\MyDcaScheduleController;
use App\Http\Controllers\MyDcaHistoryController;
use App\Http\Controllers\MyExchangeController;
use App\Http\Controllers\MyRiskController;
use Illuminate\Support\Facades\Route;

Route::match(['get', 'post'], '/', [MyController::class, 'home'])->name('home');

Route::get('dashboard', fn() => redirect('/'))->name('dashboard');

Route::get('risk-simulation', [MyRiskController::class, 'risk_simulation'])->name('risk.simulation');

Route::post('api/risk-get', [MyRiskController::class, 'risk_get_api'])->name('api.risk.get');
Route::post('api/risk-simulation', [MyRiskController::class, 'risk_simulation_api'])->name('api.risk.simulation');

Route::middleware('auth')->group(function () {
	Route::get('binance-proxy/{id}', [MyController::class, 'binance_proxy'])->name('binance.proxy');
	Route::get('get-dca-history', [MyDcaHistoryController::class, 'get_all'])->name('get.dca_history');

	Route::post('api/key-check', [MyExchangeController::class, 'check_api'])->name('api.key.check');

	Route::resource('dcakeys', MyDcaKeyController::class);

	Route::resource('dcaschedules', MyDcaScheduleController::class);
});

/*
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
*/

require __DIR__ . '/auth.php';
