<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Letter;

use Dvsa\Olcs\Api\Service\Letter\EditorJsText;
use PHPUnit\Framework\TestCase;

final class EditorJsTextTest extends TestCase
{
    public function testPlainTextStripsTagsAndCollapsesSpace(): void
    {
        $content = ['blocks' => [
            ['type' => 'paragraph', 'data' => ['text' => '<b>A</b>&nbsp; b']],
            ['type' => 'list', 'data' => ['items' => ['one', ['content' => 'two']]]],
        ]];

        $this->assertSame('A b one two', EditorJsText::plain($content));
    }

    public function testNothingWrittenIsEmpty(): void
    {
        $this->assertSame('', EditorJsText::plain([]));
        $this->assertSame('', EditorJsText::plain(['blocks' => [['type' => 'paragraph', 'data' => ['text' => ' <br> ']]]]));
    }
}
