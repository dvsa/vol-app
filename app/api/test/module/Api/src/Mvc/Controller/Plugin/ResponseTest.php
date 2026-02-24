<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Mvc\Controller\Plugin;

use Dvsa\Olcs\Api\Domain\Command\Result as CommandResult;
use Dvsa\Olcs\Api\Domain\QueryHandler\Result as QueryResult;
use Dvsa\Olcs\Api\Entity\Venue as VenueEntity;
use Dvsa\Olcs\Api\Mvc\Controller\Plugin;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Logging\Log\Logger;
use org\bovigo\vfs\vfsStream;
use Laminas\Http\Headers;
use Laminas\Http\Response as HttpResponse;
use Laminas\View\Model\JsonModel;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Mvc\Controller\Plugin\Response::class)]
class ResponseTest extends MockeryTestCase
{
    /** @var  Plugin\Response */
    protected $sut;
    /** @var  HttpResponse */
    private $response;

    public function setUp(): void
    {
        $this->response = new HttpResponse();

        $this->sut = m::mock(Plugin\Response::class)->makePartial();
        $this->sut->shouldReceive('getController->getResponse')->andReturn($this->response);

        $logger = new \Dvsa\OlcsTest\SafeLogger();
        $logger->addWriter(new \Laminas\Log\Writer\Mock());
        Logger::setLogger($logger);

        parent::setUp();
    }

    public function testNotFound(): void
    {
        $result = $this->sut->notFound();

        $this->assertInstanceOf(HttpResponse::class, $result);
        $this->assertEquals(HttpResponse::STATUS_CODE_404, $result->getStatusCode());
    }

    public function testNotReady(): void
    {
        $result = $this->sut->notReady();

        $this->assertInstanceOf(HttpResponse::class, $result);
        $this->assertEquals(HttpResponse::STATUS_CODE_503, $result->getStatusCode());
        $this->assertNotContains('Retry-After', $result->getHeaders()->toArray());
    }

    public function testNotReadyWithRetryAfter(): void
    {
        $retryAfter = 5;

        $result = $this->sut->notReady($retryAfter);

        $this->assertInstanceOf(HttpResponse::class, $result);
        $this->assertEquals(HttpResponse::STATUS_CODE_503, $result->getStatusCode());
        $this->assertEquals(['Retry-After' => $retryAfter], $result->getHeaders()->toArray());
    }

    public function testError(): void
    {
        $result = $this->sut->error(HttpResponse::STATUS_CODE_400);

        $this->assertInstanceOf(HttpResponse::class, $result);
        $this->assertEquals(HttpResponse::STATUS_CODE_400, $result->getStatusCode());
    }

    public function testErrorWithMessages(): void
    {
        $messages = ['MSG1'];

        $result = $this->sut->error(HttpResponse::STATUS_CODE_400, $messages);

        $this->assertInstanceOf(JsonModel::class, $result);
        $this->assertEquals('{"messages":["MSG1"]}', $result->serialize());
        $this->assertEquals(HttpResponse::STATUS_CODE_400, $this->response->getStatusCode());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('getSingleResultDataProvider')]
    public function testSingleResult(mixed $data): void
    {
        $result = $this->sut->singleResult($data);

        $this->assertInstanceOf(JsonModel::class, $result);
        $this->assertEquals('["item"]', $result->serialize());
        $this->assertEquals(HttpResponse::STATUS_CODE_200, $this->response->getStatusCode());
    }

    public static function getSingleResultDataProvider(): array
    {
        return [
            // array
            [
                ['item']
            ],
            // QueryResult
            [
                m::mock(QueryResult::class)->shouldReceive('serialize')->andReturn(['item'])->getMock()
            ],
            // Entity
            [
                m::mock(VenueEntity::class)->shouldReceive('jsonSerialize')->andReturn(['item'])->getMock()
            ],
        ];
    }

    public function testMultipleResults(): void
    {
        $result = $this->sut->multipleResults(2, ['item1', 'item2'], 10, ['extra1']);

        $this->assertInstanceOf(JsonModel::class, $result);
        $this->assertEquals(
            '{"count":2,"results":["item1","item2"],"count-unfiltered":10,"extra":["extra1"]}',
            $result->serialize()
        );
        $this->assertEquals(HttpResponse::STATUS_CODE_200, $this->response->getStatusCode());
    }

    #[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
    public function testStreamResult(): void
    {
        $streanContent = 'UNIT_expect_content';
        $streamHeaders = [
            'Content-Type: unittest/plain',
            'X-UnitTest: expect',
        ];

        $vfs = vfsStream::setup('temp');
        $tmpFilePath = vfsStream::newFile('stream')
            ->withContent($streanContent)
            ->at($vfs)
            ->url();
        $fh = fopen($tmpFilePath, 'rb');

        $mockHeaders = new Headers();
        $mockHeaders->addHeaders($streamHeaders);

        $mockSteam = m::mock(HttpResponse\Stream::class);
        $mockSteam->shouldReceive('getHeaders')->once()->andReturn($mockHeaders);
        $mockSteam->shouldReceive('getStream')->times(1)->andReturn($fh);

        ob_start();
        $actual = $this->sut->streamResult($mockSteam);

        $output = ob_get_contents();
        ob_end_clean();

        static::assertEquals(false, $actual);

        static::assertEquals($streanContent, $output);
    }

    public function testSuccessfulUpdate(): void
    {
        $data = new CommandResult();
        $data->addId('item', 1);
        $data->addMessage('msg');

        $result = $this->sut->successfulUpdate($data);

        $this->assertInstanceOf(JsonModel::class, $result);
        $this->assertEquals('{"id":{"item":1},"messages":["msg"]}', $result->serialize());
        $this->assertEquals(HttpResponse::STATUS_CODE_200, $this->response->getStatusCode());
    }

    public function testSuccessfulCreate(): void
    {
        $data = new CommandResult();
        $data->addId('item', 1);
        $data->addMessage('msg');

        $result = $this->sut->successfulCreate($data);

        $this->assertInstanceOf(JsonModel::class, $result);
        $this->assertEquals('{"id":{"item":1},"messages":["msg"]}', $result->serialize());
        $this->assertEquals(HttpResponse::STATUS_CODE_201, $this->response->getStatusCode());
    }

    public function testXmlAccepted(): void
    {
        $result = $this->sut->xmlAccepted();

        $this->assertInstanceOf(HttpResponse::class, $result);
        $this->assertEquals(HttpResponse::STATUS_CODE_202, $result->getStatusCode());
    }

    public function testXmlBadRequest(): void
    {
        $result = $this->sut->xmlBadRequest();

        $this->assertInstanceOf(HttpResponse::class, $result);
        $this->assertEquals(HttpResponse::STATUS_CODE_400, $result->getStatusCode());
    }
}
