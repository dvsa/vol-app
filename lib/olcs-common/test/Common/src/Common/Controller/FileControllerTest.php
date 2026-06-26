<?php

namespace CommonTest\Controller;

use Common\Controller\FileController;
use Common\Service\Cqrs\Response;
use Dvsa\Olcs\Transfer\Query as TransferQry;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Laminas\Mvc\Controller\Plugin;

/**
 * @covers Common\Controller\FileController
 */
class FileControllerTest extends TestCase
{
    /** @var  m\MockInterface */
    private $mockParams;

    /** @var  m\MockInterface */
    private $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->mockParams = m::mock(Plugin\Params::class . '[fromRoute, fromQuery]');

        $this->sut = m::mock(FileController::class . '[handleQuery, params, notFoundAction]');
        $this->sut->shouldReceive('params')->andReturn($this->mockParams);
    }

    public function testDownloadOk(): void
    {
        $id = '99999';

        $this->mockParams
            ->shouldReceive('fromRoute')->once()->with('identifier')->andReturn($id)
            ->shouldReceive('fromQuery')->once()->with('inline')->andReturn(1)
            ->shouldReceive('fromQuery')->once()->with('slug')->andReturn(0);

        $origResponse = new \Laminas\Http\Response();
        $origResponse->getHeaders()->addHeaderLine('should', 'not-appear');
        $origResponse->getHeaders()->addHeaderLine('Content-Length', 'CONTENT_LENGTH');
        $origResponse->getHeaders()->addHeaderLine('Content-Disposition', 'CONTENT_DISPOSITION');
        $origResponse->getHeaders()->addHeaderLine('Content-Type', 'CONTENT_TYPE');
        $origResponse->getHeaders()->addHeaderLine('foo', 'bar');
        $origResponse->setContent('CONTENT');

        $mockResp = m::mock(Response::class)
            ->shouldReceive('isOk')->once()->andReturn(true)
            ->shouldReceive('getHttpResponse')->once()->andReturn($origResponse)
            ->getMock();

        $this->sut
            ->shouldReceive('handleQuery')
            ->once()
            ->andReturnUsing(
                static function ($arg) use ($id, $mockResp) {
                    static::assertInstanceOf(TransferQry\Document\Download ::class, $arg);
                    /** @var TransferQry\Document\Download $arg */
                    static::assertEquals($id, $arg->getIdentifier());
                    static::assertTrue($arg->isInline());
                    return $mockResp;
                }
            );

        /** @var \Laminas\Http\Response $response */
        $response = $this->sut->downloadAction();

        static::assertCount(3, $response->getHeaders());
        static::assertSame('CONTENT_LENGTH', $response->getHeaders()->get('Content-Length')->getFieldValue());
        static::assertSame('CONTENT_DISPOSITION', $response->getHeaders()->get('Content-Disposition')->getFieldValue());
        static::assertSame('CONTENT_TYPE', $response->getHeaders()->get('Content-Type')->getFieldValue());
        static::assertSame('CONTENT', $response->getContent());
    }

    public function testDownloadGuideOk(): void
    {
        $identifier = 'ABCDE12345';

        $this->mockParams
            ->shouldReceive('fromRoute')->once()->with('identifier')->andReturn(base64_encode($identifier))
            ->shouldReceive('fromQuery')->once()->with('inline')->andReturn(0)
            ->shouldReceive('fromQuery')->once()->with('slug')->andReturn(1);

        $origResponse = new \Laminas\Http\Response();
        $origResponse->getHeaders()->addHeaderLine('should', 'not-appear');
        $origResponse->getHeaders()->addHeaderLine('Content-Length', 'CONTENT_LENGTH');
        $origResponse->getHeaders()->addHeaderLine('Content-Disposition', 'CONTENT_DISPOSITION');
        $origResponse->getHeaders()->addHeaderLine('Content-Type', 'CONTENT_TYPE');
        $origResponse->getHeaders()->addHeaderLine('foo', 'bar');
        $origResponse->setContent('CONTENT');

        $mockResp = m::mock(Response::class)
            ->shouldReceive('isOk')->once()->andReturn(true)
            ->shouldReceive('getHttpResponse')->once()->andReturn($origResponse)
            ->getMock();

        $this->sut
            ->shouldReceive('handleQuery')
            ->once()
            ->andReturnUsing(
                static function ($arg) use ($identifier, $mockResp) {
                    static::assertInstanceOf(TransferQry\Document\DownloadGuide::class, $arg);
                    /** @var TransferQry\Document\DownloadGuide $arg */
                    static::assertEquals($identifier, $arg->getIdentifier());
                    static::assertFalse($arg->isInline());
                    static::assertTrue($arg->getIsSlug());
                    return $mockResp;
                }
            );

        /** @var \Laminas\Http\Response $response */
        $response = $this->sut->downloadAction();

        static::assertCount(3, $response->getHeaders());
        static::assertSame('CONTENT_LENGTH', $response->getHeaders()->get('Content-Length')->getFieldValue());
        static::assertSame('CONTENT_DISPOSITION', $response->getHeaders()->get('Content-Disposition')->getFieldValue());
        static::assertSame('CONTENT_TYPE', $response->getHeaders()->get('Content-Type')->getFieldValue());
        static::assertSame('CONTENT', $response->getContent());
    }

    public function testFailExceptionErrDownload(): void
    {
        $identifier = '8999';

        $this->mockParams
            ->shouldReceive('fromRoute')->once()->with('identifier')->andReturn($identifier)
            ->shouldReceive('fromQuery')->andReturn(null);

        $mockResp = m::mock(Response::class)
            ->shouldReceive('isOk')->once()->andReturn(false)
            ->getMock();

        $this->sut->shouldReceive('handleQuery')->once()->andReturn($mockResp);

        static::expectException(\RuntimeException::class);

        static::assertEquals('EXPECTED_ERR_NOT_FOUND', $this->sut->downloadAction());
    }
}
