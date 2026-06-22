<?php

namespace OlcsTest\Logging\Log\Processor;

use DateTimeImmutable;
use Laminas\Http\Header\HeaderInterface;
use Laminas\Http\PhpEnvironment\Request;
use Laminas\Stdlib\RequestInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Monolog\Level;
use Monolog\LogRecord;
use Olcs\Logging\Log\Processor\CorrelationId;

class CorrelationIdTest extends TestCase
{
    public function testProcess(): void
    {
        $mockHeader = m::mock(HeaderInterface::class);
        $mockHeader->expects('getFieldValue')->withNoArgs()->andReturn('COR_ID');

        $mockRequest = m::mock(Request::class);
        $mockRequest->expects('getHeader')->with('X-Correlation-Id')->andReturn($mockHeader);

        $sut = new CorrelationId($mockRequest);

        // run first time
        $result = $sut(new LogRecord(new DateTimeImmutable(), 'test', Level::Info, ''));
        $this->assertSame('COR_ID', $result->extra['correlationId']);

        // run again to check cache property (getHeader should only be called once)
        $result = $sut(new LogRecord(new DateTimeImmutable(), 'test', Level::Info, ''));
        $this->assertSame('COR_ID', $result->extra['correlationId']);
    }

    public function testProcessCli(): void
    {
        $mockRequest = m::mock(RequestInterface::class);

        $sut = new CorrelationId($mockRequest);

        $result = $sut(new LogRecord(new DateTimeImmutable(), 'test', Level::Info, ''));

        $this->assertNull($result->extra['correlationId']);
    }
}
