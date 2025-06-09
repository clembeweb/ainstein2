<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Console\Commands\AiRunBatch;

class AiArticleServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->commands([
            AiRunBatch::class,
        ]);
    }
}
