<?php

namespace App\Jobs;

use App\Models\JsonSchemaResult;
use App\Services\TokenService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class GenerateJsonSchema implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public JsonSchemaResult $result,
        public string $serialKey = '',
        public string $businessInfo = ''
    ) {
    }

    public function handle(TokenService $tokens): void
    {
        $scrape = Http::post(
            'https://api.clementeteodonno.it/wp-json/myplugin/v1/scrape-url?user_key=' . $this->serialKey,
            ['url' => $this->result->url]
        )->body();

        $prompt = [
            'prompt_text_system' => $this->businessInfo,
            'prompt_text'       => '**URL pagina**: ' . $this->result->url . "\n\n**Contenuto Pagina:**\n" . $scrape,
            'chatgpt_model'     => 'gpt-4-turbo',
        ];

        $response = Http::post(
            'https://api.clementeteodonno.it/wp-json/myplugin/v1/generate-category-description?user_key=' . $this->serialKey,
            $prompt
        )->json();

        $schema = $response['result']['choices'][0]['message']['content'] ?? '';
        $schema = str_replace('```', '', $schema);

        $this->result->update(['schema' => $schema]);

        $tokens->record(strlen($schema), 'json-schema');
    }
}
