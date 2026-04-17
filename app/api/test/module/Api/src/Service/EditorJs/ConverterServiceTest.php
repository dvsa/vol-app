<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\EditorJs;

use Dvsa\Olcs\Api\Service\EditorJs\ConverterService;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Service\EditorJs\ConverterService::class)]
class ConverterServiceTest extends TestCase
{
    private ConverterService $sut;

    protected function setUp(): void
    {
        $this->sut = new ConverterService();
    }

    public function testConvertJsonToHtmlWithEmptyString(): void
    {
        $result = $this->sut->convertJsonToHtml('');
        $this->assertEquals('', $result);
    }

    public function testConvertJsonToHtmlWithValidJson(): void
    {
        $json = json_encode([
            'time' => 1234567890,
            'version' => '2.28.2',
            'blocks' => [
                [
                    'id' => 'test-id',
                    'type' => 'paragraph',
                    'data' => ['text' => 'Test content']
                ]
            ]
        ]);

        $result = $this->sut->convertJsonToHtml($json);

        // Test that our wrapper calls the external lib and returns HTML
        $this->assertIsString($result);
        $this->assertStringContainsString('Test content', $result);
    }

    public function testConvertJsonToHtmlWithInvalidJson(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to convert JSON to HTML');

        $this->sut->convertJsonToHtml('invalid json syntax {{{');
    }

    public function testServiceInstantiatesWithoutDependencies(): void
    {
        // Test that our wrapper service can be instantiated
        $service = new ConverterService();
        $this->assertInstanceOf(ConverterService::class, $service);
    }

    public function testCleanOutputHtmlIsCalled(): void
    {
        $json = json_encode([
            'time' => 1234567890,
            'version' => '2.28.2',
            'blocks' => [
                [
                    'id' => 'empty-id',
                    'type' => 'paragraph',
                    'data' => ['text' => '']
                ], // Empty paragraph
                [
                    'id' => 'valid-id',
                    'type' => 'paragraph',
                    'data' => ['text' => 'Valid content']
                ]
            ]
        ]);

        $result = $this->sut->convertJsonToHtml($json);

        // Test that our cleanOutputHtml method works
        $this->assertStringNotContainsString('<p></p>', $result);
        $this->assertStringContainsString('Valid content', $result);
    }
}
