<?php

namespace Dvsa\OlcsTest\Api\Service\Document\Parser;

use Dvsa\Olcs\Api\Service\Document\Parser\EditorJsParser;
use PHPUnit\Framework\TestCase;

/**
 * EditorJsParser Test
 *
 */
class EditorJsParserTest extends TestCase
{
    private EditorJsParser $parser;

    public function setUp(): void
    {
        $this->parser = new EditorJsParser();
    }

    public function testGetFileExtension()
    {
        $this->assertEquals('json', $this->parser->getFileExtension());
    }

    public function testRenderImageThrowsException()
    {
        $this->expectException(\RuntimeException::class);
        $this->parser->renderImage('data', 100, 100, 'png');
    }

    public function testExtractTokensFromParagraph()
    {
        $json = json_encode([
            'blocks' => [
                [
                    'type' => 'paragraph',
                    'data' => [
                        'text' => 'Dear [[Operator Name]], your licence [[Licence Number]]'
                    ]
                ]
            ]
        ]);

        $tokens = $this->parser->extractTokens($json);

        $this->assertCount(2, $tokens);
        $this->assertContains('Operator Name', $tokens);
        $this->assertContains('Licence Number', $tokens);
    }

    public function testExtractTokensFromHeader()
    {
        $json = json_encode([
            'blocks' => [
                [
                    'type' => 'header',
                    'data' => [
                        'text' => 'Licence [[Licence Number]]',
                        'level' => 2
                    ]
                ]
            ]
        ]);

        $tokens = $this->parser->extractTokens($json);

        $this->assertCount(1, $tokens);
        $this->assertContains('Licence Number', $tokens);
    }

    public function testExtractTokensFromList()
    {
        $json = json_encode([
            'blocks' => [
                [
                    'type' => 'list',
                    'data' => [
                        'style' => 'unordered',
                        'items' => [
                            'Operator: [[Operator Name]]',
                            'Date: [[Current Date]]',
                            'Caseworker: [[Caseworker Name]]'
                        ]
                    ]
                ]
            ]
        ]);

        $tokens = $this->parser->extractTokens($json);

        $this->assertCount(3, $tokens);
        $this->assertContains('Operator Name', $tokens);
        $this->assertContains('Current Date', $tokens);
        $this->assertContains('Caseworker Name', $tokens);
    }

    public function testExtractTokensFromMultipleBlocks()
    {
        $json = json_encode([
            'blocks' => [
                [
                    'type' => 'paragraph',
                    'data' => ['text' => 'Dear [[Operator Name]]']
                ],
                [
                    'type' => 'header',
                    'data' => ['text' => 'Licence [[Licence Number]]', 'level' => 2]
                ],
                [
                    'type' => 'list',
                    'data' => [
                        'style' => 'unordered',
                        'items' => ['Date: [[Current Date]]']
                    ]
                ]
            ]
        ]);

        $tokens = $this->parser->extractTokens($json);

        $this->assertCount(3, $tokens);
        $this->assertContains('Operator Name', $tokens);
        $this->assertContains('Licence Number', $tokens);
        $this->assertContains('Current Date', $tokens);
    }

    public function testExtractTokensDeduplicates()
    {
        $json = json_encode([
            'blocks' => [
                [
                    'type' => 'paragraph',
                    'data' => ['text' => '[[Operator Name]] and [[Operator Name]] again']
                ]
            ]
        ]);

        $tokens = $this->parser->extractTokens($json);

        $this->assertCount(1, $tokens);
        $this->assertContains('Operator Name', $tokens);
    }

    public function testExtractTokensHandlesInvalidJson()
    {
        $tokens = $this->parser->extractTokens('not valid json{');

        $this->assertIsArray($tokens);
        $this->assertEmpty($tokens);
    }

    public function testExtractTokensHandlesEmptyBlocks()
    {
        $json = json_encode(['blocks' => []]);

        $tokens = $this->parser->extractTokens($json);

        $this->assertIsArray($tokens);
        $this->assertEmpty($tokens);
    }

    public function testReplaceTokensInParagraph()
    {
        $json = json_encode([
            'blocks' => [
                [
                    'type' => 'paragraph',
                    'data' => [
                        'text' => 'Dear [[Operator Name]], your licence [[Licence Number]]'
                    ]
                ]
            ]
        ]);

        $data = [
            'Operator Name' => ['content' => 'Joe Bloggs Transport Ltd', 'preformatted' => false],
            'Licence Number' => ['content' => 'OB1234567', 'preformatted' => false]
        ];

        $result = $this->parser->replace($json, $data);
        $decoded = json_decode($result, true);

        $expected = 'Dear Joe Bloggs Transport Ltd, your licence OB1234567';
        $this->assertEquals($expected, $decoded['blocks'][0]['data']['text']);
    }

    public function testReplaceTokensInHeader()
    {
        $json = json_encode([
            'blocks' => [
                [
                    'type' => 'header',
                    'data' => [
                        'text' => 'Licence [[Licence Number]]',
                        'level' => 2
                    ]
                ]
            ]
        ]);

        $data = [
            'Licence Number' => ['content' => 'OB1234567', 'preformatted' => false]
        ];

        $result = $this->parser->replace($json, $data);
        $decoded = json_decode($result, true);

        $this->assertEquals('Licence OB1234567', $decoded['blocks'][0]['data']['text']);
        $this->assertEquals(2, $decoded['blocks'][0]['data']['level']);
    }

    public function testReplaceTokensInList()
    {
        $json = json_encode([
            'blocks' => [
                [
                    'type' => 'list',
                    'data' => [
                        'style' => 'unordered',
                        'items' => [
                            'Operator: [[Operator Name]]',
                            'Date: [[Current Date]]'
                        ]
                    ]
                ]
            ]
        ]);

        $data = [
            'Operator Name' => ['content' => 'Joe Bloggs', 'preformatted' => false],
            'Current Date' => ['content' => '01/01/2025', 'preformatted' => false]
        ];

        $result = $this->parser->replace($json, $data);
        $decoded = json_decode($result, true);

        $this->assertEquals('Operator: Joe Bloggs', $decoded['blocks'][0]['data']['items'][0]);
        $this->assertEquals('Date: 01/01/2025', $decoded['blocks'][0]['data']['items'][1]);
    }

    public function testReplaceTokensWithSimpleStringValues()
    {
        $json = json_encode([
            'blocks' => [
                ['type' => 'paragraph', 'data' => ['text' => '[[Token]]']]
            ]
        ]);

        // Test with simple string instead of array format
        $data = ['Token' => 'Simple Value'];

        $result = $this->parser->replace($json, $data);
        $decoded = json_decode($result, true);

        $this->assertEquals('Simple Value', $decoded['blocks'][0]['data']['text']);
    }

    public function testReplaceTokensConvertsNewlinesToBr()
    {
        $json = json_encode([
            'blocks' => [
                ['type' => 'paragraph', 'data' => ['text' => '[[Address]]']]
            ]
        ]);

        $data = [
            'Address' => ['content' => "Line 1\nLine 2\nLine 3", 'preformatted' => false]
        ];

        $result = $this->parser->replace($json, $data);
        $decoded = json_decode($result, true);

        $this->assertEquals('Line 1<br>Line 2<br>Line 3', $decoded['blocks'][0]['data']['text']);
    }

    public function testReplaceTokensPreservesPreformattedNewlines()
    {
        $json = json_encode([
            'blocks' => [
                ['type' => 'paragraph', 'data' => ['text' => '[[Content]]']]
            ]
        ]);

        $data = [
            'Content' => ['content' => "Line 1\nLine 2", 'preformatted' => true]
        ];

        $result = $this->parser->replace($json, $data);
        $decoded = json_decode($result, true);

        $this->assertEquals("Line 1\nLine 2", $decoded['blocks'][0]['data']['text']);
    }

    public function testReplaceTokensLeavesUnknownTokensIntact()
    {
        $json = json_encode([
            'blocks' => [
                [
                    'type' => 'paragraph',
                    'data' => ['text' => '[[Known]] and [[Unknown]]']
                ]
            ]
        ]);

        $data = [
            'Known' => ['content' => 'Replaced', 'preformatted' => false]
        ];

        $result = $this->parser->replace($json, $data);
        $decoded = json_decode($result, true);

        $this->assertEquals('Replaced and [[Unknown]]', $decoded['blocks'][0]['data']['text']);
    }

    public function testReplaceHandlesInvalidJson()
    {
        $invalidJson = 'not valid json{';

        $result = $this->parser->replace($invalidJson, ['Token' => 'Value']);

        // Should return original content unchanged
        $this->assertEquals($invalidJson, $result);
    }

    public function testReplaceHandlesEmptyData()
    {
        $json = json_encode([
            'blocks' => [
                ['type' => 'paragraph', 'data' => ['text' => '[[Token]]']]
            ]
        ]);

        $result = $this->parser->replace($json, []);
        $decoded = json_decode($result, true);

        // Token should remain unchanged
        $this->assertEquals('[[Token]]', $decoded['blocks'][0]['data']['text']);
    }

    public function testReplacePreservesJsonStructure()
    {
        $json = json_encode([
            'time' => 1234567890,
            'version' => '2.28.0',
            'blocks' => [
                [
                    'id' => 'test-id-123',
                    'type' => 'paragraph',
                    'data' => ['text' => '[[Token]]']
                ]
            ]
        ]);

        $data = ['Token' => 'Value'];

        $result = $this->parser->replace($json, $data);
        $decoded = json_decode($result, true);

        // Check structure is preserved
        $this->assertEquals(1234567890, $decoded['time']);
        $this->assertEquals('2.28.0', $decoded['version']);
        $this->assertEquals('test-id-123', $decoded['blocks'][0]['id']);
        $this->assertEquals('Value', $decoded['blocks'][0]['data']['text']);
    }
}
