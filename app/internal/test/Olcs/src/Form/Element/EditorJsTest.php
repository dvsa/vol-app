<?php

declare(strict_types=1);

namespace OlcsTest\Form\Element;

use Olcs\Form\Element\EditorJs;
use Olcs\Form\Element\EditorJsFactory;
use Olcs\Service\EditorJs\HtmlConverter;
use PHPUnit\Framework\TestCase;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Olcs\Form\Element\EditorJs::class)]
class EditorJsTest extends TestCase
{
    private EditorJs $sut;
    private $mockHtmlConverter;

    protected function setUp(): void
    {
        $this->mockHtmlConverter = m::mock(HtmlConverter::class);

        $mockServiceManager = m::mock(\Laminas\ServiceManager\ServiceManager::class);
        $mockServiceManager->shouldReceive('get')
            ->with(HtmlConverter::class)
            ->andReturn($this->mockHtmlConverter);

        $factory = new EditorJsFactory();
        $this->sut = $factory($mockServiceManager, EditorJs::class);
    }

    public function testGetInputSpecification(): void
    {
        $spec = $this->sut->getInputSpecification();

        $this->assertIsArray($spec);
        $this->assertArrayHasKey('name', $spec);
        $this->assertArrayHasKey('filters', $spec);
        $this->assertArrayHasKey('validators', $spec);
    }

    public function testSetValueWithValidJson(): void
    {
        $validJson = json_encode([
            'blocks' => [['type' => 'paragraph', 'data' => ['text' => 'Test']]]
        ]);

        $this->sut->setValue($validJson);

        $this->assertEquals($validJson, $this->sut->getValue());
    }

    public function testSetValueWithHtml(): void
    {
        $html = '<p>Test paragraph</p>';
        $expectedJson = json_encode(['blocks' => []]);

        $this->mockHtmlConverter->shouldReceive('convertHtmlToJson')
            ->once()
            ->with($html)
            ->andReturn($expectedJson);

        $this->sut->setValue($html);

        $this->assertEquals($expectedJson, $this->sut->getValue());
    }

    public function testSetValueWithNull(): void
    {
        $this->sut->setValue(null);
        $this->assertNull($this->sut->getValue());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('isValidEditorJsJsonProvider')]
    public function testIsValidEditorJsJson(mixed $input, mixed $expected): void
    {
        $result = $this->sut->isValidEditorJsJson($input);
        $this->assertEquals($expected, $result);
    }

    public static function isValidEditorJsJsonProvider(): array
    {
        return [
            'valid json' => [
                json_encode(['blocks' => []]),
                true
            ],
            'invalid json' => [
                'not json',
                false
            ],
            'html content' => [
                '<p>HTML</p>',
                false
            ],
        ];
    }
}
