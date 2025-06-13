<?php

namespace App\Services;

use App\Models\SeoContent;

class SeoContentService
{
    public function __construct(protected TokenService $tokens) {}

    public function generate(SeoContent $content): void
    {
        $this->tokens->consume(1);

        $content->update([
            'seo_description'      => 'Description for ' . $content->name,
            'seo_title'            => 'Title for ' . $content->name,
            'seo_meta_description' => 'Meta for ' . $content->name,
            'check_result'         => 'ok',
        ]);
    }
}
