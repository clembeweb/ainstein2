<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AiArticleJob;
use App\Services\AiArticleService;

class AiRunBatch extends Command
{
    protected $signature = 'ai:run-batch {--limit=10}';
    protected $description = 'Process AI article jobs queue';

    public function handle(AiArticleService $ai): int
    {
        AiArticleJob::where('status','!=','done')
            ->limit($this->option('limit'))
            ->get()
            ->each(fn ($job) => $ai->handle($job));

        $this->info('Batch completed');
        return self::SUCCESS;
    }
}
