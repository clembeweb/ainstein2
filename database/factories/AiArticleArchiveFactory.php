<?php

namespace Database\Factories;

use App\Models\AiArticleArchive;
use App\Models\AiArticleJob;
use Illuminate\Database\Eloquent\Factories\Factory;

class AiArticleArchiveFactory extends Factory
{
    protected $model = AiArticleArchive::class;

    public function definition(): array
    {
        return [
            'ai_article_job_id' => AiArticleJob::factory(),
            'keyword' => $this->faker->word(),
            'html' => '<p>'.$this->faker->sentence().'</p>',
        ];
    }
}
