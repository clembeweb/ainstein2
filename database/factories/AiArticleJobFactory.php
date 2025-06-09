<?php

namespace Database\Factories;

use App\Models\AiArticleJob;
use Illuminate\Database\Eloquent\Factories\Factory;

class AiArticleJobFactory extends Factory
{
    protected $model = AiArticleJob::class;

    public function definition(): array
    {
        return [
            'keyword' => $this->faker->words(3, true),
            'status' => 'pending',
            'log' => null,
        ];
    }
}
