<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NegativeKeyword extends Model
{
    protected $fillable = [
        'user_id',
        'campaign_strategy_id',
        'adgroup_name',
        'keyword',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(CampaignStrategy::class, 'campaign_strategy_id');
    }
}
