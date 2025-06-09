<?php

namespace App\Services;

use App\Models\AiArticleJob;
use App\Models\AiArticleStep;
use App\Services\Concerns\HasAiArticleHelpers;
use OpenAI\Client as OpenAI;
use SerpApi\Search;

class AiArticleService
{
    use HasAiArticleHelpers;

    public function __construct(
        protected Search $serp,
        protected OpenAI  $openai,
    ) {}

    public function handle(AiArticleJob $job, int $from = 1, int $to = 7): void
    {
        $job->update(['status' => 'running']);
        $steps = $job->step ?? AiArticleStep::create(['ai_article_job_id' => $job->id]);

        try {
            if ($from <= 1 && $to >= 1 && !$steps->step1) {
                $steps->step1 = $this->step1_serp($job->keyword);
                $steps->save();
            }
            /* …aggiungi step2-7 simili… */
            $job->update(['status' => 'done']);
        } catch (\Throwable $e) {
            $job->update([
                'status' => 'error',
                'log'    => $e->getMessage()
            ]);
        }
    }

    protected function step1_serp(string $kw): array
    {
        return $this->serp->get_json([
            'engine'    => 'google',
            'q'         => $kw,
            'hl'        => 'it',
            'api_key'   => env('SERP_API_KEY'),
        ]);
    }
}
