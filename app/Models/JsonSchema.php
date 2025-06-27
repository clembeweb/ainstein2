<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JsonSchema extends Model
{
    protected $fillable = [
        'user_id',
        'payload',
        'result',
        'status',
        'tokens_used',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
