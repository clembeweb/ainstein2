<?php

namespace Tests\Feature;

use App\Jobs\GenerateJsonSchema;
use App\Livewire\AiGenerateJsonSchema;
use App\Models\JsonSchemaResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class JsonSchemaGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_upload_creates_results(): void
    {
        $xml = '<?xml version="1.0"?><urlset><url><loc>https://example.com/a</loc></url><url><loc>https://example.com/b</loc></url></urlset>';
        $file = UploadedFile::fake()->createWithContent('sitemap.xml', $xml);

        Livewire::test(AiGenerateJsonSchema::class)
            ->set('sitemap', $file)
            ->call('uploadSitemap');

        $this->assertEquals([
            'https://example.com/a',
            'https://example.com/b',
        ], JsonSchemaResult::pluck('url')->toArray());
    }

    public function test_job_generates_schema_and_tracks_tokens(): void
    {
        Http::fake([
            '*/scrape-url*' => Http::response('content'),
            '*/generate-category-description*' => Http::response([
                'result' => [
                    'choices' => [
                        ['message' => ['content' => '```schema```']],
                    ],
                ],
            ], 200),
        ]);

        $result = JsonSchemaResult::create(['url' => 'https://example.com/a']);

        GenerateJsonSchema::dispatchSync($result, 'key', 'info');

        $this->assertEquals('schema', $result->fresh()->schema);
        $this->assertDatabaseCount('token_logs', 1);
    }
}
