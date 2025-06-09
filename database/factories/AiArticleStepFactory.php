<?php

namespace Database\Factories;

use App\Models\AiArticleJob;
use App\Models\AiArticleStep;
use Illuminate\Database\Eloquent\Factories\Factory;

class AiArticleStepFactory extends Factory
{
    protected $model = AiArticleStep::class;

    public function definition(): array
    {
        return [
            'ai_article_job_id' => AiArticleJob::factory(),
            'step1' => null,
            'step2' => null,
            'step3' => null,
            'step4' => null,
            'step5' => null,
            'step6' => null,
            'step7' => null,
        ];
    }
}
