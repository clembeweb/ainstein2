<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\JsonSchemaResult;
use App\Jobs\GenerateJsonSchema;

class AiGenerateJsonSchema extends Component
{
    use WithFileUploads;

    public $sitemap;
    public $serialKey = '';
    public $businessInfo = '';

    protected $rules = [
        'sitemap' => 'required|file|mimes:xml',
    ];

    public function uploadSitemap(): void
    {
        $this->validate();
        $xml = simplexml_load_string($this->sitemap->get());
        foreach ($xml->url as $url) {
            JsonSchemaResult::firstOrCreate([
                'url' => (string) $url->loc,
            ]);
        }
    }

    public function generate(): void
    {
        $results = JsonSchemaResult::whereNull('schema')->get();
        foreach ($results as $result) {
            GenerateJsonSchema::dispatch($result, $this->serialKey, $this->businessInfo);
        }
    }

    public function delete(int $id): void
    {
        JsonSchemaResult::whereKey($id)->delete();
    }

    public function render()
    {
        return view('livewire.ai-generate-json-schema', [
            'results' => JsonSchemaResult::all(),
        ]);
    }
}
