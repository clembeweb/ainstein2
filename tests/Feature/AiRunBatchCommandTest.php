<?php

namespace Tests\Feature;

use App\Services\AiArticleService;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AiRunBatchCommandTest extends TestCase
{
    public function test_command_outputs_batch_completed(): void
    {
        $this->app->bind(AiArticleService::class, fn() => new class extends AiArticleService {
            public function __construct() {}
            public function handle(\App\Models\AiArticleJob $job, int $from = 1, int $to = 7): void {}
        });

        Artisan::call('ai:run-batch', ['--limit' => 0]);

        $this->assertStringContainsString('Batch completed', Artisan::output());
    }
}
