<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::middleware(['web','auth:sanctum','verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::redirect('/', '/dashboard');
    Route::prefix('tools')->group(function () {
        Route::get('/seo-content', \App\Livewire\SeoContentManager::class)
            ->name('seo-content');
        Route::get('/json-schema', \App\Livewire\Tools\JsonSchemaGenerator::class)
            ->name('json-schema');
        Route::get('/negative-keywords', \App\Livewire\Tools\NegativeKeywordsManager::class)
            ->name('negative-keywords');
    });
});
