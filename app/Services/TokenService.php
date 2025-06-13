<?php

namespace App\Services;

use App\Models\User;

class TokenService
{
    protected int $used = 0;

    public function __construct(protected ?User $user = null) {}

    public function consume(int $amount): void
    {
        // In real implementation this would decrement token balance
        $this->used += $amount;
    }

    public function used(): int
    {
        return $this->used;
    }
}
