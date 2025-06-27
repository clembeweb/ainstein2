<?php

namespace App\Livewire\Tools;

use Livewire\Component;
use App\Models\JsonSchema;
use App\Services\TokenService;
use OpenAI\Client as OpenAI;

class JsonSchemaGenerator extends Component
{
    public string $urls = '';
    public $list;

    public function mount(): void
    {
        $this->load();
    }

    public function load(): void
    {
        $this->list = JsonSchema::latest()->get();
    }

    public function generate(TokenService $tokens, OpenAI $openai): void
    {
        $this->validate(['urls' => 'required|string']);

        $tokens->consume(1);

        $result = json_encode(['generated' => true], JSON_PRETTY_PRINT);

        JsonSchema::create([
            'user_id'     => auth()->id(),
            'payload'     => $this->urls,
            'result'      => $result,
            'status'      => 'done',
            'tokens_used' => 1,
        ]);

        $this->urls = '';
        $this->load();
    }

    public function render()
    {
        return view('tools.json-schema');
    }
}
