<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoogleAdsToken extends Model
{
    protected $fillable = [
        'user_id',
        'access_token',
        'refresh_token',
        'expires_at',
    ];

    protected $casts = [
        'access_token' => 'array',
        'expires_at'   => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
