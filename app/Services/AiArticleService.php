<?php

namespace App\Services;

use App\Models\AiArticleArchive;
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

            if ($from <= 2 && $to >= 2 && !$steps->step2) {
                $steps->step2 = $this->step2_extractUrls($steps->step1);
                $steps->save();
            }

            if ($from <= 3 && $to >= 3 && !$steps->step3) {
                $steps->step3 = $this->step3_generateOutline($job->keyword, $steps->step2);
                $steps->save();
            }

            if ($from <= 4 && $to >= 4 && !$steps->step4) {
                $steps->step4 = $this->step4_generateDraft($job->keyword, $steps->step3);
                $steps->save();
            }

            if ($from <= 5 && $to >= 5 && !$steps->step5) {
                $steps->step5 = $this->step5_improveDraft($steps->step4);
                $steps->save();
            }

            if ($from <= 6 && $to >= 6 && !$steps->step6) {
                $steps->step6 = $this->step6_finalizeHtml($steps->step5);
                $steps->save();
            }

            if ($from <= 7 && $to >= 7 && !$steps->step7) {
                $steps->step7 = $this->step7_archive($job, $steps->toArray());
                $steps->save();
            }

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
            'api_key'   => config('services.serp.key'),
        ]);
    }

    protected function step2_extractUrls(array $step1): array
    {
        $urls = collect($step1['organic_results'] ?? [])
            ->pluck('link')
            ->take(3)
            ->values()
            ->all();

        return ['urls' => $urls];
    }

    protected function step3_generateOutline(string $keyword, array $step2): array
    {
        $outline = 'Outline for ' . $keyword . ' using ' . implode(', ', $step2['urls'] ?? []);

        return ['outline' => $outline];
    }

    protected function step4_generateDraft(string $keyword, array $step3): array
    {
        $html = '<h1>' . e($keyword) . '</h1><p>' . e($step3['outline'] ?? '') . '</p>';

        return ['html' => $html];
    }

    protected function step5_improveDraft(array $step4): array
    {
        $html = ($step4['html'] ?? '') . '<!-- improved -->';

        return ['html' => $html];
    }

    protected function step6_finalizeHtml(array $step5): array
    {
        return ['html' => $step5['html'] ?? ''];
    }

    protected function step7_archive(AiArticleJob $job, array $steps): array
    {
        $html = $this->bestHtml($steps);

        AiArticleArchive::updateOrCreate(
            ['ai_article_job_id' => $job->id],
            ['keyword' => $job->keyword, 'html' => $html]
        );

        return ['html' => $html];
    }
}
