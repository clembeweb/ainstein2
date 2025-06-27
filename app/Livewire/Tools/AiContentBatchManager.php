<?php

namespace App\Livewire\Tools;

use Livewire\Component;
use App\Models\AiContentBatch;
use App\Services\TokenService;
use App\Services\WordpressConnector;
use OpenAI\Client as OpenAI;

class AiContentBatchManager extends Component
{
    public string $siteUrl = '';
    public string $payload = '';
    public $list;

    public function mount(): void
    {
        $this->load();
    }

    public function load(): void
    {
        $this->list = AiContentBatch::latest()->get();
    }

    public function generate(TokenService $tokens, OpenAI $openai, WordpressConnector $wp): void
    {
        $this->validate([
            'siteUrl' => 'required|url',
            'payload' => 'required|string',
        ]);

        $tokens->consume(1);

        $result = 'Generated post for: ' . $this->payload;
        $wp->publish($this->siteUrl, ['content' => $result]);

        AiContentBatch::create([
            'user_id'     => auth()->id(),
            'payload'     => $this->payload,
            'result'      => $result,
            'status'      => 'done',
            'tokens_used' => 1,
        ]);

        $this->payload = '';
        $this->load();
    }

    public function render()
    {
        return view('tools.ai-content-batch');
    }
}
