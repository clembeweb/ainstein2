<?php

namespace Tests\Feature\Tools;

use App\Models\User;
use App\Services\TokenService;
use Livewire\Livewire;
use App\Livewire\Tools\NegativeKeywordsManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ToolsAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_routes_are_accessible(): void
    {
        $this->actingAs(User::factory()->create());

        $routes = ['negative-keywords', 'seo-content', 'json-schema', 'ai-content-batches'];

        foreach ($routes as $route) {
            $this->get(route($route))->assertStatus(200);
        }
    }

    public function test_generate_consumes_tokens(): void
    {
        $this->actingAs($user = User::factory()->create());

        $service = new TokenService($user);
        $this->app->instance(TokenService::class, $service);

        Livewire::test(NegativeKeywordsManager::class)
            ->set('payload', 'foo')
            ->call('generate');

        $this->assertEquals(1, $service->used());
        $this->assertDatabaseHas('negative_keywords', ['payload' => 'foo']);
    }
}
