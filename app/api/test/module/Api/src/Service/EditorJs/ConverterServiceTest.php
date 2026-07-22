<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\EditorJs;

use Dvsa\Olcs\Api\Service\EditorJs\ConverterService;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Service\EditorJs\ConverterService::class)]
final class ConverterServiceTest extends TestCase
{
    private ConverterService $sut;

    protected function setUp(): void
    {
        $this->sut = new ConverterService();
    }

    public function testConvertJsonToHtmlWithEmptyString(): void
    {
        $result = $this->sut->convertJsonToHtml('');
        $this->assertSame('', $result);
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

    /**
     * Genuine 1x1 PNG: the purifier's data-scheme handler verifies the payload
     * decodes to a real image, so a truncated string would be dropped.
     */
    private function dataUriImageJson(): string
    {
        return json_encode([
            'time' => 1234567890,
            'version' => '2.28.2',
            'blocks' => [
                [
                    'id' => 'logo-block',
                    'type' => 'paragraph',
                    'data' => [
                        'text' => '<img src="data:image/png;base64,'
                            . 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
                            . '" alt="OTC logo">'
                    ]
                ]
            ]
        ]);
    }

    public function testConvertJsonToHtmlKeepsDataUriImagesWhenInlineImagesAllowed(): void
    {
        // Chrome slots (admin-authored) embed the OTC logo as an inline base64 image.
        $result = $this->sut->convertJsonToHtml($this->dataUriImageJson(), true);

        $this->assertStringContainsString('<img', $result);
        $this->assertStringContainsString('data:image/png;base64', $result);
    }

    public function testConvertJsonToHtmlStripsDataUriImagesByDefault(): void
    {
        // Caseworker-editable content (sections/issues/todos) must NOT gain the
        // ability to smuggle arbitrary inline images into official letters.
        $result = $this->sut->convertJsonToHtml($this->dataUriImageJson());

        $this->assertStringNotContainsString('data:image', $result);
    }

    public function testNormalizeFillsMissingEnvelopeFields(): void
    {
        // Shape produced by hand-authored seeds: no top-level time, no per-block ids —
        // both mandatory for the Setono parser.
        $data = [
            'version' => '2.28.2',
            'blocks' => [
                ['type' => 'paragraph', 'data' => ['text' => 'Office of the Traffic Commissioner']],
                ['type' => 'paragraph', 'data' => ['text' => 'Leeds']],
            ],
        ];

        $normalized = $this->sut->normalize($data);

        $this->assertIsInt($normalized['time']);
        $this->assertSame('2.28.2', $normalized['version']);
        foreach ($normalized['blocks'] as $i => $block) {
            $this->assertNotSame('', (string) $block['id'], "block $i id should be non-empty");
            $this->assertSame($data['blocks'][$i]['data'], $block['data']);
        }
    }

    public function testNormalizeLeavesConformantDataUntouched(): void
    {
        $data = [
            'time' => 1234567890,
            'version' => '2.31.0',
            'blocks' => [
                ['id' => 'abc123', 'type' => 'paragraph', 'data' => ['text' => 'Hello']],
            ],
        ];

        $this->assertSame($data, $this->sut->normalize($data));
    }

    public function testNormalizedSeedShapeConverts(): void
    {
        $seedShaped = [
            'version' => '2.28.2',
            'blocks' => [
                ['type' => 'paragraph', 'data' => ['text' => 'Quarry House']],
            ],
        ];

        $html = $this->sut->convertJsonToHtml(json_encode($this->sut->normalize($seedShaped)));

        $this->assertStringContainsString('Quarry House', $html);
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

    public function testPurifierConfigUsesWritableSerializerPath(): void
    {
        $config = $this->sut->buildPurifierConfig();

        $serializerPath = $config->get('Cache.SerializerPath');

        // Must not be left at HTMLPurifier's default (inside the read-only vendor tree in deployed
        // containers), which causes "not writable" warnings and per-request cache regeneration.
        $this->assertSame(sys_get_temp_dir(), $serializerPath);
        $this->assertDirectoryIsWritable($serializerPath);
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
