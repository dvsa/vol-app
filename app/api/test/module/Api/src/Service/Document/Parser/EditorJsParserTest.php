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
                        'text' => 'Dear [[OP_NAME]], your licence [[LICENCE_NUMBER]]'
                    ]
                ]
            ]
        ]);

        $tokens = $this->parser->extractTokens($json);

        $this->assertCount(2, $tokens);
        $this->assertContains('OP_NAME', $tokens);
        $this->assertContains('LICENCE_NUMBER', $tokens);
    }

    public function testExtractTokensFromHeader()
    {
        $json = json_encode([
            'blocks' => [
                [
                    'type' => 'header',
                    'data' => [
                        'text' => 'Licence [[LICENCE_NUMBER]]',
                        'level' => 2
                    ]
                ]
            ]
        ]);

        $tokens = $this->parser->extractTokens($json);

        $this->assertCount(1, $tokens);
        $this->assertContains('LICENCE_NUMBER', $tokens);
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
                            'Operator: [[OP_NAME]]',
                            'Date: [[TODAYS_DATE]]',
                            'Caseworker: [[CASEWORKER_NAME]]'
                        ]
                    ]
                ]
            ]
        ]);

        $tokens = $this->parser->extractTokens($json);

        $this->assertCount(3, $tokens);
        $this->assertContains('OP_NAME', $tokens);
        $this->assertContains('TODAYS_DATE', $tokens);
        $this->assertContains('CASEWORKER_NAME', $tokens);
    }

    public function testExtractTokensFromMultipleBlocks()
    {
        $json = json_encode([
            'blocks' => [
                [
                    'type' => 'paragraph',
                    'data' => ['text' => 'Dear [[OP_NAME]]']
                ],
                [
                    'type' => 'header',
                    'data' => ['text' => 'Licence [[LICENCE_NUMBER]]', 'level' => 2]
                ],
                [
                    'type' => 'list',
                    'data' => [
                        'style' => 'unordered',
                        'items' => ['Date: [[TODAYS_DATE]]']
                    ]
                ]
            ]
        ]);

        $tokens = $this->parser->extractTokens($json);

        $this->assertCount(3, $tokens);
        $this->assertContains('OP_NAME', $tokens);
        $this->assertContains('LICENCE_NUMBER', $tokens);
        $this->assertContains('TODAYS_DATE', $tokens);
    }

    public function testExtractTokensDeduplicates()
    {
        $json = json_encode([
            'blocks' => [
                [
                    'type' => 'paragraph',
                    'data' => ['text' => '[[OP_NAME]] and [[OP_NAME]] again']
                ]
            ]
        ]);

        $tokens = $this->parser->extractTokens($json);

        $this->assertCount(1, $tokens);
        $this->assertContains('OP_NAME', $tokens);
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
                        'text' => 'Dear [[OP_NAME]], your licence [[LICENCE_NUMBER]]'
                    ]
                ]
            ]
        ]);

        $data = [
            'OP_NAME' => ['content' => 'Joe Bloggs Transport Ltd', 'preformatted' => false],
            'LICENCE_NUMBER' => ['content' => 'OB1234567', 'preformatted' => false]
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
                        'text' => 'Licence [[LICENCE_NUMBER]]',
                        'level' => 2
                    ]
                ]
            ]
        ]);

        $data = [
            'LICENCE_NUMBER' => ['content' => 'OB1234567', 'preformatted' => false]
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
                            'Operator: [[OP_NAME]]',
                            'Date: [[TODAYS_DATE]]'
                        ]
                    ]
                ]
            ]
        ]);

        $data = [
            'OP_NAME' => ['content' => 'Joe Bloggs', 'preformatted' => false],
            'TODAYS_DATE' => ['content' => '01/01/2025', 'preformatted' => false]
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
                ['type' => 'paragraph', 'data' => ['text' => '[[TEST_TOKEN]]']]
            ]
        ]);

        // Test with simple string instead of array format
        $data = ['TEST_TOKEN' => 'Simple Value'];

        $result = $this->parser->replace($json, $data);
        $decoded = json_decode($result, true);

        $this->assertEquals('Simple Value', $decoded['blocks'][0]['data']['text']);
    }

    public function testReplaceTokensConvertsNewlinesToBr()
    {
        $json = json_encode([
            'blocks' => [
                ['type' => 'paragraph', 'data' => ['text' => '[[ADDRESS]]']]
            ]
        ]);

        $data = [
            'ADDRESS' => ['content' => "Line 1\nLine 2\nLine 3", 'preformatted' => false]
        ];

        $result = $this->parser->replace($json, $data);
        $decoded = json_decode($result, true);

        $this->assertEquals('Line 1<br>Line 2<br>Line 3', $decoded['blocks'][0]['data']['text']);
    }

    public function testReplaceTokensPreservesPreformattedNewlines()
    {
        $json = json_encode([
            'blocks' => [
                ['type' => 'paragraph', 'data' => ['text' => '[[CONTENT]]']]
            ]
        ]);

        $data = [
            'CONTENT' => ['content' => "Line 1\nLine 2", 'preformatted' => true]
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
                    'data' => ['text' => '[[KNOWN]] and [[UNKNOWN]]']
                ]
            ]
        ]);

        $data = [
            'KNOWN' => ['content' => 'Replaced', 'preformatted' => false]
        ];

        $result = $this->parser->replace($json, $data);
        $decoded = json_decode($result, true);

        $this->assertEquals('Replaced and [[UNKNOWN]]', $decoded['blocks'][0]['data']['text']);
    }

    public function testReplaceHandlesInvalidJson()
    {
        $invalidJson = 'not valid json{';

        $result = $this->parser->replace($invalidJson, ['TEST_TOKEN' => 'Value']);

        // Should return original content unchanged
        $this->assertEquals($invalidJson, $result);
    }

    public function testReplaceHandlesEmptyData()
    {
        $json = json_encode([
            'blocks' => [
                ['type' => 'paragraph', 'data' => ['text' => '[[TEST_TOKEN]]']]
            ]
        ]);

        $result = $this->parser->replace($json, []);
        $decoded = json_decode($result, true);

        // Token should remain unchanged
        $this->assertEquals('[[TEST_TOKEN]]', $decoded['blocks'][0]['data']['text']);
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
                    'data' => ['text' => '[[TEST_TOKEN]]']
                ]
            ]
        ]);

        $data = ['TEST_TOKEN' => 'Value'];

        $result = $this->parser->replace($json, $data);
        $decoded = json_decode($result, true);

        // Check structure is preserved
        $this->assertEquals(1234567890, $decoded['time']);
        $this->assertEquals('2.28.0', $decoded['version']);
        $this->assertEquals('test-id-123', $decoded['blocks'][0]['id']);
        $this->assertEquals('Value', $decoded['blocks'][0]['data']['text']);
    }
}
