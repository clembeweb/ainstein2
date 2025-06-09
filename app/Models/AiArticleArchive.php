<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\AiArticleJob;

class AiArticleArchive extends Model
{
    protected $fillable = [
        'ai_article_job_id',
        'keyword',
        'html',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(AiArticleJob::class);
    }
}

