<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/** @mixin \Illuminate\Database\Eloquent\Builder */

class AiArticleJob extends Model
{
    protected $fillable = [
        'keyword',
        'status',
        'log',
    ];

    public function step(): HasOne
    {
        return $this->hasOne(AiArticleStep::class);
    }

    public function archive(): HasOne
    {
        return $this->hasOne(AiArticleArchive::class);
    }
}
