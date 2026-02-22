<?php

declare(strict_types=1);

namespace OlcsTest\Service\EditorJs;

use Olcs\Service\EditorJs\HtmlConverter;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Olcs\Service\EditorJs\HtmlConverter::class)]
class HtmlConverterTest extends TestCase
{
    private HtmlConverter $sut;

    protected function setUp(): void
    {
        $this->sut = new HtmlConverter();
    }

    public function testConvertHtmlToJsonWithEmptyString(): void
    {
        $result = $this->sut->convertHtmlToJson('');
        $decoded = json_decode($result, true);

        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('blocks', $decoded);
        $this->assertEmpty($decoded['blocks']);
    }

    public function testConvertSimpleHtml(): void
    {
        $html = '<p>Test paragraph</p>';

        $result = $this->sut->convertHtmlToJson($html);
        $decoded = json_decode($result, true);

        $this->assertCount(1, $decoded['blocks']);
        $this->assertEquals('paragraph', $decoded['blocks'][0]['type']);
        $this->assertEquals('Test paragraph', $decoded['blocks'][0]['data']['text']);
    }

    public function testConvertPlainText(): void
    {
        $plainText = 'Just plain text without tags';

        $result = $this->sut->convertHtmlToJson($plainText);
        $decoded = json_decode($result, true);

        $this->assertCount(1, $decoded['blocks']);
        $this->assertEquals('paragraph', $decoded['blocks'][0]['type']);
        $this->assertEquals($plainText, $decoded['blocks'][0]['data']['text']);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('htmlElementsProvider')]
    public function testConvertDifferentElements(mixed $html, mixed $expectedType, mixed $expectedData): void
    {
        $result = $this->sut->convertHtmlToJson($html);
        $decoded = json_decode($result, true);

        $this->assertEquals($expectedType, $decoded['blocks'][0]['type']);
        $this->assertEquals($expectedData, $decoded['blocks'][0]['data']);
    }

    public static function htmlElementsProvider(): array
    {
        return [
            'header h2' => [
                '<h2>Test Header</h2>',
                'header',
                ['text' => 'Test Header', 'level' => 2]
            ],
            'unordered list' => [
                '<ul><li>Item 1</li><li>Item 2</li></ul>',
                'list',
                ['style' => 'unordered', 'items' => ['Item 1', 'Item 2']]
            ],
            'ordered list' => [
                '<ol><li>First</li><li>Second</li></ol>',
                'list',
                ['style' => 'ordered', 'items' => ['First', 'Second']]
            ],
        ];
    }

    public function testHandlesEncodingIssues(): void
    {
        $html = '<p>reg  31 to be considered</p>';

        $result = $this->sut->convertHtmlToJson($html);
        $decoded = json_decode($result, true);

        // Multiple spaces are normalized to single space
        $this->assertEquals('reg 31 to be considered', $decoded['blocks'][0]['data']['text']);
    }

    public function testPreservesFormatting(): void
    {
        $html = '<p>Text with <b>bold</b> and <i>italic</i></p>';

        $result = $this->sut->convertHtmlToJson($html);
        $decoded = json_decode($result, true);

        $this->assertStringContainsString('<b>bold</b>', $decoded['blocks'][0]['data']['text']);
        $this->assertStringContainsString('<i>italic</i>', $decoded['blocks'][0]['data']['text']);
    }

    public function testComplexDocument(): void
    {
        $html = '
            <h1>Title</h1>
            <p>Paragraph one</p>
            <ul><li>List item</li></ul>
            <p>Paragraph two</p>
        ';

        $result = $this->sut->convertHtmlToJson($html);
        $decoded = json_decode($result, true);

        $this->assertCount(4, $decoded['blocks']);
        $this->assertEquals('header', $decoded['blocks'][0]['type']);
        $this->assertEquals('paragraph', $decoded['blocks'][1]['type']);
        $this->assertEquals('list', $decoded['blocks'][2]['type']);
        $this->assertEquals('paragraph', $decoded['blocks'][3]['type']);
    }
}
