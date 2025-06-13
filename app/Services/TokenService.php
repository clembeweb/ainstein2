<?php

namespace App\Services;

use App\Models\TokenLog;

class TokenService
{
    public function record(int $tokens, ?string $action = null): void
    {
        TokenLog::create([
            'tokens' => $tokens,
            'action' => $action,
        ]);
    }
}
