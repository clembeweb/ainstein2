<?php

namespace App\Jobs;

use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\SeoContent;
use App\Services\SeoContentService;

class GenerateSeoContent
{
    use Dispatchable;
    public function __construct(protected SeoContent $content) {}

    public function handle(SeoContentService $service): void
    {
        $service->generate($this->content);
    }
}
