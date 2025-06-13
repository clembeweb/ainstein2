<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use SerpApi\Search;
use OpenAI\Client as OpenAIClient;
use OpenAI;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(Search::class, function (): Search {
            return new Search(['api_key' => config('services.serp.key')]);
        });

        $this->app->singleton(OpenAIClient::class, function (): OpenAIClient {
            return OpenAI::client(config('services.openai.key'));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
       Blade::component('components.banner', 'banner');

    }
}
