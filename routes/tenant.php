<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::middleware(['web','auth:sanctum','verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::redirect('/', '/dashboard');
    Route::get('/ai-json-schema', \App\Livewire\AiGenerateJsonSchema::class)->name('ai-json-schema');
});
