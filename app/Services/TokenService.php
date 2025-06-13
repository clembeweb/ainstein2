<?php

namespace App\Services;

use Stancl\Tenancy\Database\Models\Tenant;

class TokenService
{
    /**
     * Get the current token balance for the tenant.
     */
    public function balance(?Tenant $tenant = null): int
    {
        $tenant = $tenant ?: tenant();

        return (int) ($tenant->token_balance ?? 0);
    }

    /**
     * Deduct tokens from the tenant balance.
     */
    public function deduct(int $amount, ?Tenant $tenant = null): int
    {
        $tenant = $tenant ?: tenant();
        $balance = $this->balance($tenant) - $amount;
        $tenant->token_balance = $balance;
        $tenant->save();

        return $balance;
    }

    /**
     * Restore tokens back to the tenant balance.
     */
    public function restore(int $amount, ?Tenant $tenant = null): int
    {
        $tenant = $tenant ?: tenant();
        $balance = $this->balance($tenant) + $amount;
        $tenant->token_balance = $balance;
        $tenant->save();

        return $balance;
    }
}
