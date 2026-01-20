<?php

namespace Dvsa\OlcsTest\Api\Service\Letter;

use Dvsa\Olcs\Api\Domain\QueryHandlerManager;
use Dvsa\Olcs\Api\Service\Date as DateService;
use Dvsa\Olcs\Api\Service\Document\Bookmark\BookmarkFactory;
use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;
use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\StaticBookmark;
use Dvsa\Olcs\Api\Service\Document\Bookmark\Interfaces\DateHelperAwareInterface;
use Dvsa\Olcs\Api\Service\Letter\VolGrabReplacementService;
use Dvsa\Olcs\Api\Domain\TranslatorAwareInterface;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Laminas\I18n\Translator\TranslatorInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Logging\Log\Logger;

/**
 * VolGrabReplacementService Test
 */
class VolGrabReplacementServiceTest extends MockeryTestCase
{
    private VolGrabReplacementService $service;
    private $mockBookmarkFactory;
    private $mockQueryHandler;
    private $mockDateService;
    private $mockTranslator;

    public function setUp(): void
    {
        $this->mockBookmarkFactory = m::mock(BookmarkFactory::class);
        $this->mockQueryHandler = m::mock(QueryHandlerManager::class);
        $this->mockDateService = m::mock(DateService::class);
        $this->mockTranslator = m::mock(TranslatorInterface::class);

        $this->service = new VolGrabReplacementService(
            $this->mockBookmarkFactory,
            $this->mockQueryHandler,
            $this->mockDateService,
            $this->mockTranslator
        );

        // Initialize Logger properly (Logger is static)
        $logWriter = new \Laminas\Log\Writer\Mock();
        $logger = new \Laminas\Log\Logger();
        $logger->addWriter($logWriter);
        Logger::setLogger($logger);
    }

    public function tearDown(): void
    {
        m::close();
    }

    public function testReplaceGrabsReturnsEmptyStringWhenGivenEmptyString()
    {
        $result = $this->service->replaceGrabs('', []);

        $this->assertEquals('', $result);
    }

    public function testReplaceGrabsReturnsOriginalJsonWhenNoTokensFound()
    {
        $json = json_encode(['blocks' => [['type' => 'paragraph', 'data' => ['text' => 'Hello World']]]]);

        $result = $this->service->replaceGrabs($json, ['licence' => 7]);

        $this->assertEquals($json, $result);
    }

    public function testReplaceGrabsSuccessfullyReplacesStaticBookmark()
    {
        $json = json_encode([
            'blocks' => [
                ['type' => 'paragraph', 'data' => ['text' => 'Date: [[TODAYS_DATE]]']]
            ]
        ]);

        // Mock static bookmark
        $mockBookmark = m::mock(StaticBookmark::class);
        $mockBookmark->shouldReceive('setParser')->once();
        $mockBookmark->shouldReceive('isStatic')->andReturn(true);
        $mockBookmark->shouldReceive('render')->andReturn('11/11/2025');
        $mockBookmark->shouldReceive('isPreformatted')->andReturn(false);

        $this->mockBookmarkFactory->shouldReceive('locate')
            ->with('TODAYS_DATE')
            ->once()
            ->andReturn($mockBookmark);

        $result = $this->service->replaceGrabs($json, ['user' => 1]);
        $decoded = json_decode($result, true);

        $this->assertStringContainsString('11/11/2025', $decoded['blocks'][0]['data']['text']);
        $this->assertStringNotContainsString('[[TODAYS_DATE]]', $decoded['blocks'][0]['data']['text']);
    }

    public function testReplaceGrabsSuccessfullyReplacesDynamicBookmark()
    {
        $json = json_encode([
            'blocks' => [
                ['type' => 'paragraph', 'data' => ['text' => 'Operator: [[OP_NAME]]']]
            ]
        ]);

        // Mock dynamic bookmark
        $mockBookmark = m::mock(DynamicBookmark::class, DateHelperAwareInterface::class);
        $mockBookmark->shouldReceive('setParser')->once();
        $mockBookmark->shouldReceive('setDateHelper')->with($this->mockDateService)->once();
        $mockBookmark->shouldReceive('isStatic')->andReturn(false);

        $mockQuery = m::mock(QueryInterface::class);
        $mockBookmark->shouldReceive('validateDataAndGetQuery')
            ->with(['licence' => 7, 'user' => 1])
            ->once()
            ->andReturn($mockQuery);

        $queryResult = ['organisation' => ['name' => 'Test Company Ltd']];
        $this->mockQueryHandler->shouldReceive('handleQuery')
            ->with($mockQuery)
            ->once()
            ->andReturn($queryResult);

        $mockBookmark->shouldReceive('setData')->with($queryResult)->once();
        $mockBookmark->shouldReceive('render')->andReturn('Test Company Ltd');
        $mockBookmark->shouldReceive('isPreformatted')->andReturn(false);

        $this->mockBookmarkFactory->shouldReceive('locate')
            ->with('OP_NAME')
            ->once()
            ->andReturn($mockBookmark);

        $result = $this->service->replaceGrabs($json, ['licence' => 7, 'user' => 1]);
        $decoded = json_decode($result, true);

        $this->assertStringContainsString('Test Company Ltd', $decoded['blocks'][0]['data']['text']);
        $this->assertStringNotContainsString('[[OP_NAME]]', $decoded['blocks'][0]['data']['text']);
    }

    public function testReplaceGrabsInjectsTranslatorInterfaceIntoBookmark()
    {
        $json = json_encode([
            'blocks' => [
                ['type' => 'paragraph', 'data' => ['text' => '[[TEST_TOKEN]]']]
            ]
        ]);

        $mockBookmark = m::mock(StaticBookmark::class, TranslatorAwareInterface::class);
        $mockBookmark->shouldReceive('setParser')->once();
        $mockBookmark->shouldReceive('setTranslator')->with($this->mockTranslator)->once();
        $mockBookmark->shouldReceive('isStatic')->andReturn(true);
        $mockBookmark->shouldReceive('render')->andReturn('Translated Value');
        $mockBookmark->shouldReceive('isPreformatted')->andReturn(false);

        $this->mockBookmarkFactory->shouldReceive('locate')
            ->with('TEST_TOKEN')
            ->once()
            ->andReturn($mockBookmark);

        $result = $this->service->replaceGrabs($json, []);

        $this->assertNotEmpty($result);
    }

    public function testReplaceGrabsHandlesBookmarkCreationException()
    {
        $json = json_encode([
            'blocks' => [
                ['type' => 'paragraph', 'data' => ['text' => '[[UNKNOWN_TOKEN]]']]
            ]
        ]);

        $this->mockBookmarkFactory->shouldReceive('locate')
            ->with('UNKNOWN_TOKEN')
            ->once()
            ->andThrow(new \Exception('Bookmark class not found'));

        // Should log warning but continue processing
        $result = $this->service->replaceGrabs($json, []);
        $decoded = json_decode($result, true);

        // Unknown token should remain in place
        $this->assertStringContainsString('[[UNKNOWN_TOKEN]]', $decoded['blocks'][0]['data']['text']);
    }

    public function testReplaceGrabsHandlesQueryExecutionException()
    {
        $json = json_encode([
            'blocks' => [
                ['type' => 'paragraph', 'data' => ['text' => '[[OP_NAME]]']]
            ]
        ]);

        $mockBookmark = m::mock(DynamicBookmark::class);
        $mockBookmark->shouldReceive('setParser')->once();
        $mockBookmark->shouldReceive('isStatic')->andReturn(false);

        $mockQuery = m::mock(QueryInterface::class);
        $mockBookmark->shouldReceive('validateDataAndGetQuery')
            ->with(['licence' => 999])
            ->once()
            ->andReturn($mockQuery);

        $this->mockQueryHandler->shouldReceive('handleQuery')
            ->with($mockQuery)
            ->once()
            ->andThrow(new \Exception('Database connection failed'));

        $this->mockBookmarkFactory->shouldReceive('locate')
            ->with('OP_NAME')
            ->once()
            ->andReturn($mockBookmark);

        // Should log error but continue processing
        $result = $this->service->replaceGrabs($json, ['licence' => 999]);
        $decoded = json_decode($result, true);

        // Token should remain since query failed
        $this->assertStringContainsString('[[OP_NAME]]', $decoded['blocks'][0]['data']['text']);
    }

    public function testReplaceGrabsHandlesRenderException()
    {
        $json = json_encode([
            'blocks' => [
                ['type' => 'paragraph', 'data' => ['text' => '[[TEST_TOKEN]]']]
            ]
        ]);

        $mockBookmark = m::mock(StaticBookmark::class);
        $mockBookmark->shouldReceive('setParser')->once();
        $mockBookmark->shouldReceive('isStatic')->andReturn(true);
        $mockBookmark->shouldReceive('render')
            ->once()
            ->andThrow(new \Exception('Rendering failed'));

        $this->mockBookmarkFactory->shouldReceive('locate')
            ->with('TEST_TOKEN')
            ->once()
            ->andReturn($mockBookmark);

        // Should log warning but continue processing
        $result = $this->service->replaceGrabs($json, []);
        $decoded = json_decode($result, true);

        // Token should remain since rendering failed
        $this->assertStringContainsString('[[TEST_TOKEN]]', $decoded['blocks'][0]['data']['text']);
    }

    public function testReplaceGrabsHandlesTopLevelException()
    {
        // Pass invalid JSON to trigger exception in main try/catch
        $invalidJson = 'invalid{json';

        // Should catch exception and return original content
        $result = $this->service->replaceGrabs($invalidJson, []);

        $this->assertEquals($invalidJson, $result);
    }

    public function testReplaceGrabsHandlesMultipleTokensWithMixedSuccess()
    {
        $json = json_encode([
            'blocks' => [
                ['type' => 'paragraph', 'data' => ['text' => '[[GOOD_TOKEN]] and [[BAD_TOKEN]]']]
            ]
        ]);

        // Good token
        $mockGoodBookmark = m::mock(StaticBookmark::class);
        $mockGoodBookmark->shouldReceive('setParser')->once();
        $mockGoodBookmark->shouldReceive('isStatic')->andReturn(true);
        $mockGoodBookmark->shouldReceive('render')->andReturn('SUCCESS');
        $mockGoodBookmark->shouldReceive('isPreformatted')->andReturn(false);

        $this->mockBookmarkFactory->shouldReceive('locate')
            ->with('GOOD_TOKEN')
            ->once()
            ->andReturn($mockGoodBookmark);

        // Bad token throws exception
        $this->mockBookmarkFactory->shouldReceive('locate')
            ->with('BAD_TOKEN')
            ->once()
            ->andThrow(new \Exception('Bad token'));

        $result = $this->service->replaceGrabs($json, []);
        $decoded = json_decode($result, true);

        // Good token replaced, bad token remains
        $this->assertStringContainsString('SUCCESS', $decoded['blocks'][0]['data']['text']);
        $this->assertStringContainsString('[[BAD_TOKEN]]', $decoded['blocks'][0]['data']['text']);
    }

    public function testReplaceGrabsSkipsStaticBookmarksInQueryExecution()
    {
        $json = json_encode([
            'blocks' => [
                ['type' => 'paragraph', 'data' => ['text' => '[[STATIC_TOKEN]]']]
            ]
        ]);

        $mockBookmark = m::mock(StaticBookmark::class);
        $mockBookmark->shouldReceive('setParser')->once();
        $mockBookmark->shouldReceive('isStatic')->andReturn(true);
        $mockBookmark->shouldReceive('render')->andReturn('Static Value');
        $mockBookmark->shouldReceive('isPreformatted')->andReturn(false);

        $this->mockBookmarkFactory->shouldReceive('locate')
            ->with('STATIC_TOKEN')
            ->once()
            ->andReturn($mockBookmark);

        // Query handler should NOT be called for static bookmarks
        $this->mockQueryHandler->shouldNotReceive('handleQuery');

        $result = $this->service->replaceGrabs($json, []);

        $this->assertNotEmpty($result);
    }

    // Tests for replaceGrabsInHtml method

    public function testReplaceGrabsInHtmlReturnsEmptyStringWhenGivenEmptyString()
    {
        $result = $this->service->replaceGrabsInHtml('', []);

        $this->assertEquals('', $result);
    }

    public function testReplaceGrabsInHtmlReturnsOriginalHtmlWhenNoTokensFound()
    {
        $html = '<html><body><p>Hello World</p></body></html>';

        $result = $this->service->replaceGrabsInHtml($html, ['licence' => 7]);

        $this->assertEquals($html, $result);
    }

    public function testReplaceGrabsInHtmlSuccessfullyReplacesStaticBookmark()
    {
        $html = '<html><body><p>Date: [[TODAYS_DATE]]</p></body></html>';

        $mockBookmark = m::mock(StaticBookmark::class);
        $mockBookmark->shouldReceive('isStatic')->andReturn(true);
        $mockBookmark->shouldReceive('render')->andReturn('23rd January 2026');

        $this->mockBookmarkFactory->shouldReceive('locate')
            ->with('TODAYS_DATE')
            ->once()
            ->andReturn($mockBookmark);

        $result = $this->service->replaceGrabsInHtml($html, ['user' => 1]);

        $this->assertStringContainsString('23rd January 2026', $result);
        $this->assertStringNotContainsString('[[TODAYS_DATE]]', $result);
    }

    public function testReplaceGrabsInHtmlSuccessfullyReplacesDynamicBookmark()
    {
        $html = '<div>Operator: [[OP_NAME]]</div>';

        $mockBookmark = m::mock(DynamicBookmark::class, DateHelperAwareInterface::class);
        $mockBookmark->shouldReceive('setDateHelper')->with($this->mockDateService)->once();
        $mockBookmark->shouldReceive('isStatic')->andReturn(false);

        $mockQuery = m::mock(QueryInterface::class);
        $mockBookmark->shouldReceive('validateDataAndGetQuery')
            ->with(['licence' => 7])
            ->once()
            ->andReturn($mockQuery);

        $queryResult = ['organisation' => ['name' => 'Test Company Ltd']];
        $this->mockQueryHandler->shouldReceive('handleQuery')
            ->with($mockQuery)
            ->once()
            ->andReturn($queryResult);

        $mockBookmark->shouldReceive('setData')->with($queryResult)->once();
        $mockBookmark->shouldReceive('render')->andReturn('Test Company Ltd');

        $this->mockBookmarkFactory->shouldReceive('locate')
            ->with('OP_NAME')
            ->once()
            ->andReturn($mockBookmark);

        $result = $this->service->replaceGrabsInHtml($html, ['licence' => 7]);

        $this->assertStringContainsString('Test Company Ltd', $result);
        $this->assertStringNotContainsString('[[OP_NAME]]', $result);
    }

    public function testReplaceGrabsInHtmlInjectsTranslatorInterfaceIntoBookmark()
    {
        $html = '<p>[[TEST_TOKEN]]</p>';

        $mockBookmark = m::mock(StaticBookmark::class, TranslatorAwareInterface::class);
        $mockBookmark->shouldReceive('setTranslator')->with($this->mockTranslator)->once();
        $mockBookmark->shouldReceive('isStatic')->andReturn(true);
        $mockBookmark->shouldReceive('render')->andReturn('Translated Value');

        $this->mockBookmarkFactory->shouldReceive('locate')
            ->with('TEST_TOKEN')
            ->once()
            ->andReturn($mockBookmark);

        $result = $this->service->replaceGrabsInHtml($html, []);

        $this->assertStringContainsString('Translated Value', $result);
    }

    public function testReplaceGrabsInHtmlHandlesBookmarkCreationException()
    {
        $html = '<p>[[UNKNOWN_TOKEN]]</p>';

        $this->mockBookmarkFactory->shouldReceive('locate')
            ->with('UNKNOWN_TOKEN')
            ->once()
            ->andThrow(new \Exception('Bookmark class not found'));

        $result = $this->service->replaceGrabsInHtml($html, []);

        $this->assertStringContainsString('[[UNKNOWN_TOKEN]]', $result);
    }

    public function testReplaceGrabsInHtmlHandlesMultipleTokens()
    {
        $html = '<html>Date: [[TODAYS_DATE]] Name: [[OP_NAME]]</html>';

        $mockDateBookmark = m::mock(StaticBookmark::class);
        $mockDateBookmark->shouldReceive('isStatic')->andReturn(true);
        $mockDateBookmark->shouldReceive('render')->andReturn('23rd January 2026');

        $mockNameBookmark = m::mock(DynamicBookmark::class);
        $mockNameBookmark->shouldReceive('isStatic')->andReturn(false);

        $mockQuery = m::mock(QueryInterface::class);
        $mockNameBookmark->shouldReceive('validateDataAndGetQuery')
            ->with(['licence' => 123])
            ->once()
            ->andReturn($mockQuery);

        $this->mockQueryHandler->shouldReceive('handleQuery')
            ->with($mockQuery)
            ->once()
            ->andReturn(['name' => 'ACME Ltd']);

        $mockNameBookmark->shouldReceive('setData')->once();
        $mockNameBookmark->shouldReceive('render')->andReturn('ACME Ltd');

        $this->mockBookmarkFactory->shouldReceive('locate')
            ->with('TODAYS_DATE')
            ->once()
            ->andReturn($mockDateBookmark);

        $this->mockBookmarkFactory->shouldReceive('locate')
            ->with('OP_NAME')
            ->once()
            ->andReturn($mockNameBookmark);

        $result = $this->service->replaceGrabsInHtml($html, ['licence' => 123]);

        $this->assertStringContainsString('23rd January 2026', $result);
        $this->assertStringContainsString('ACME Ltd', $result);
        $this->assertStringNotContainsString('[[TODAYS_DATE]]', $result);
        $this->assertStringNotContainsString('[[OP_NAME]]', $result);
    }

    public function testReplaceGrabsInHtmlPreservesHtmlStructure()
    {
        $html = '<div class="header">
            <h1>Letter</h1>
            <p>Date: [[TODAYS_DATE]]</p>
        </div>';

        $mockBookmark = m::mock(StaticBookmark::class);
        $mockBookmark->shouldReceive('isStatic')->andReturn(true);
        $mockBookmark->shouldReceive('render')->andReturn('23rd January 2026');

        $this->mockBookmarkFactory->shouldReceive('locate')
            ->with('TODAYS_DATE')
            ->once()
            ->andReturn($mockBookmark);

        $result = $this->service->replaceGrabsInHtml($html, []);

        // HTML structure should be preserved
        $this->assertStringContainsString('<div class="header">', $result);
        $this->assertStringContainsString('<h1>Letter</h1>', $result);
        $this->assertStringContainsString('23rd January 2026', $result);
    }

    public function testReplaceGrabsInHtmlConvertsNewlinesToBrTags()
    {
        $html = '<div>Address: [[TA_ADDRESS]]</div>';

        $mockBookmark = m::mock(StaticBookmark::class);
        $mockBookmark->shouldReceive('isStatic')->andReturn(true);
        $mockBookmark->shouldReceive('render')->andReturn("Line 1\nLine 2\nLine 3");

        $this->mockBookmarkFactory->shouldReceive('locate')
            ->with('TA_ADDRESS')
            ->once()
            ->andReturn($mockBookmark);

        $result = $this->service->replaceGrabsInHtml($html, []);

        $this->assertStringContainsString('Line 1<br>Line 2<br>Line 3', $result);
        $this->assertStringNotContainsString("\n", str_replace(['<div>', '</div>'], '', $result));
    }
}
