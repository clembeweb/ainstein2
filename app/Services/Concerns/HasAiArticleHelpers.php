<?php

namespace App\Services\Concerns;

trait HasAiArticleHelpers
{
    public function bestHtml(array $steps): string
    {
        foreach (['step6','step5','step4'] as $field) {
            if (!empty($steps[$field]['html'] ?? '')) {
                return $steps[$field]['html'];
            }
        }
        return '';
    }
}
