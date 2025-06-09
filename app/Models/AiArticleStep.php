<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiArticleStep extends Model
{
    protected $fillable = [
        'ai_article_job_id',
        'step1',
        'step2',
        'step3',
        'step4',
        'step5',
        'step6',
        'step7',
    ];

    protected $casts = [
        'step1' => 'array',
        'step2' => 'array',
        'step3' => 'array',
        'step4' => 'array',
        'step5' => 'array',
        'step6' => 'array',
        'step7' => 'array',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(AiArticleJob::class);
    }
}

