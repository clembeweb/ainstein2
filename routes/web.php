<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

/*
|------------------------------------------------------------------
|  Central domain (ainstein.test)
|------------------------------------------------------------------
*/
Route::domain(config('tenancy.central_domains')[0])->group(function () {
    Route::get('/', fn () => view('welcome'));
});

/*
|------------------------------------------------------------------
|  Tenant domains  (es. acme.ainstein.test)
|------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/dashboard', DashboardController::class)->name('dashboard'); // nuova riga
});
