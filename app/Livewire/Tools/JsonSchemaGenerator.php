<?php

namespace App\Livewire\Tools;

use Livewire\Component;

class JsonSchemaGenerator extends Component
{
    public string $urls = '';
    public string $businessInfo = '';
    public array $results = [];

    public function generate(): void
    {
        $this->validate(['urls' => 'required|string']);
        $lines = preg_split("/\r?\n/", trim($this->urls));
        $this->results = [];
        foreach ($lines as $url) {
            $url = trim($url);
            if ($url === '') {
                continue;
            }
            $schema = [
                '@context' => 'https://schema.org',
                '@type' => 'WebPage',
                'url' => $url,
                'name' => 'Schema for ' . $url,
            ];
            $this->results[] = [
                'url' => $url,
                'schema' => json_encode($schema, JSON_PRETTY_PRINT),
            ];
        }
    }

    public function render()
    {
        return view('livewire.tools.json-schema-generator');
    }
}
