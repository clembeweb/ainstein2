<?php

namespace Tests\Unit;

use App\Services\Concerns\HasAiArticleHelpers;
use PHPUnit\Framework\TestCase;

class AiArticleHelpersTest extends TestCase
{
    private object $helper;

    protected function setUp(): void
    {
        $this->helper = new class {
            use HasAiArticleHelpers;
        };
    }

    public function test_best_html_returns_first_available_field(): void
    {
        $steps = [
            'step6' => ['html' => 'six'],
            'step5' => ['html' => 'five'],
            'step4' => ['html' => 'four'],
        ];

        $this->assertSame('six', $this->helper->bestHtml($steps));
    }

    public function test_best_html_returns_empty_string_if_none_present(): void
    {
        $this->assertSame('', $this->helper->bestHtml([]));
    }
}
