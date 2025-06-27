<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::middleware(['web','auth:sanctum','verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::redirect('/', '/dashboard');
    Route::get('/tools/negative-keywords',  \App\Livewire\Tools\NegativeKeywordsManager::class)
        ->name('negative-keywords');
    Route::get('/tools/seo-content',        \App\Livewire\Tools\SeoContentManager::class)
        ->name('seo-content');
    Route::get('/tools/json-schema',        \App\Livewire\Tools\JsonSchemaGenerator::class)
        ->name('json-schema');
    Route::get('/tools/ai-content-batches', \App\Livewire\Tools\AiContentBatchManager::class)
        ->name('ai-content-batches');
});
