<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Stancl\Tenancy\Database\Models\Tenant;
use Stancl\Tenancy\Database\Models\Domain;
use Tests\TestCase;

class TenancyMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_central_domain_is_blocked_from_tenant_routes(): void
    {
        Tenant::create(['id' => 'acme']);
        Domain::create(['domain' => 'acme.localhost', 'tenant_id' => 'acme']);

        $response = $this->get('http://localhost/dashboard');

        $response->assertForbidden();
    }

    public function test_tenant_domain_allows_access_to_tenant_routes(): void
    {
        Tenant::create(['id' => 'acme']);
        Domain::create(['domain' => 'acme.localhost', 'tenant_id' => 'acme']);

        $response = $this->get('http://acme.localhost/dashboard');

        $response->assertStatus(302); // redirected to login
    }
}
