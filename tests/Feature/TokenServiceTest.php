<?php

namespace Tests\Feature;

use App\Services\TokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Stancl\Tenancy\Database\Models\Tenant;
use Tests\TestCase;

class TokenServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_tokens_are_deducted_from_balance(): void
    {
        $tenant = Tenant::create(['id' => 'acme']);
        $tenant->token_balance = 10;
        $tenant->save();

        $service = new TokenService();
        $service->deduct(3, $tenant);

        $this->assertSame(7, $service->balance($tenant));
        $this->assertSame(7, $tenant->fresh()->token_balance);
    }

    public function test_tokens_can_be_restored(): void
    {
        $tenant = Tenant::create(['id' => 'acme']);
        $tenant->token_balance = 5;
        $tenant->save();

        $service = new TokenService();
        $service->deduct(2, $tenant);
        $service->restore(2, $tenant);

        $this->assertSame(5, $service->balance($tenant));
        $this->assertSame(5, $tenant->fresh()->token_balance);
    }
}
