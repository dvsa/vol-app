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

    /**
     * Whether a save changed what the letter says: the editor restamps its time and can give blocks
     * new ids without anything changing
     */
    public function testSameContentIgnoresTheEditorsBookkeeping(): void
    {
        $saved = ['time' => 1, 'version' => '2.31.0', 'blocks' => [
            ['id' => 'a1', 'type' => 'paragraph', 'data' => ['text' => 'Dear <b>ACME</b>']],
        ]];
        $resaved = ['blocks' => [
            ['data' => ['text' => 'Dear <b>ACME</b>'], 'type' => 'paragraph', 'id' => 'z9'],
        ], 'time' => 2];

        $this->assertTrue(EditorJsText::sameContent($saved, $resaved));
    }

    public static function changes(): \Iterator
    {
        yield 'wording' => ['Dear <b>ACME</b> Ltd', 'paragraph', []];
        yield 'formatting' => ['Dear ACME', 'paragraph', []];
        yield 'block type' => ['Dear <b>ACME</b>', 'header', []];
        yield 'numbers that only look equal' => ['01', 'paragraph', [], '1'];
        yield 'alignment' => ['Dear <b>ACME</b>', 'paragraph', ['alignment' => ['alignment' => 'right']]];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('changes')]
    public function testSameContentSeesAnyChangeToWhatIsPrinted(
        string $text,
        string $type,
        array $tunes,
        string $was = 'Dear <b>ACME</b>'
    ): void {
        $saved = ['blocks' => [['id' => 'a1', 'type' => 'paragraph', 'data' => ['text' => $was]]]];
        $changed = ['blocks' => [['id' => 'a1', 'type' => $type, 'data' => ['text' => $text]] + ($tunes === [] ? [] : ['tunes' => $tunes])]];

        $this->assertFalse(EditorJsText::sameContent($saved, $changed));
    }

    public function testSameContentSeesABlockAddedOrRemoved(): void
    {
        $one = ['blocks' => [['type' => 'paragraph', 'data' => ['text' => 'A']]]];
        $two = ['blocks' => [['type' => 'paragraph', 'data' => ['text' => 'A']], ['type' => 'paragraph', 'data' => ['text' => '']]]];

        $this->assertFalse(EditorJsText::sameContent($one, $two));
        $this->assertFalse(EditorJsText::sameContent([], $one));
        $this->assertTrue(EditorJsText::sameContent([], ['blocks' => []]));
    }
}
