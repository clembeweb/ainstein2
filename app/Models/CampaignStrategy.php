<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignStrategy extends Model
{
    protected $fillable = [
        'user_id',
        'google_campaign_id',
        'campaign_name',
        'strategy',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function negativeKeywords(): HasMany
    {
        return $this->hasMany(NegativeKeyword::class);
    }
}
