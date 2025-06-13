<?php

namespace Tests\Feature;

use App\Livewire\SeoContentManager;
use App\Jobs\GenerateSeoContent;
use App\Models\SeoContent;
use App\Services\TokenService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class SeoContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_csv_upload_creates_records(): void
    {
        $this->actingAs(User::factory()->create());

        $csv = UploadedFile::fake()->createWithContent('seo.csv', "name,url\nFoo,https://foo.com\nBar,https://bar.com\n");

        Livewire::test(SeoContentManager::class)
            ->set('file', $csv)
            ->call('uploadCsv');

        $this->assertDatabaseHas('seo_contents', ['name' => 'Foo']);
        $this->assertDatabaseHas('seo_contents', ['name' => 'Bar']);
    }

    public function test_content_generation_updates_record(): void
    {
        $this->actingAs($user = User::factory()->create());

        $content = SeoContent::create(['name' => 'Foo', 'url' => 'https://foo.com']);

        $this->app->bind(TokenService::class, fn() => new TokenService($user));
        GenerateSeoContent::dispatchSync($content);

        $updated = $content->fresh();
        $this->assertNotNull($updated->seo_description);
        $this->assertSame('ok', $updated->check_result);
    }

    public function test_data_can_be_updated(): void
    {
        $this->actingAs(User::factory()->create());

        $content = SeoContent::create(['name' => 'Foo', 'url' => 'https://foo.com']);

        Livewire::test(SeoContentManager::class)
            ->call('updateField', $content->id, 'seo_title', 'New Title');

        $this->assertSame('New Title', $content->fresh()->seo_title);
    }
}
