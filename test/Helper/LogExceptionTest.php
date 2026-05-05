<?php

namespace OlcsTest\Logging\Helper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Logging\Helper\LogException;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class LogExceptionTest extends TestCase
{
    public function testLogException(): void
    {
        $e3 = new \Exception('3rd error');
        $e2 = new \Exception('nested error', 22, $e3);
        $e1 = new \Exception('error', 11, $e2);

        $mockLog = m::mock(LoggerInterface::class);
        $mockLog->shouldReceive('info')->ordered('logcalls')->with('', ['exception' => $e3]);
        $mockLog->shouldReceive('info')->ordered('logcalls')->with('', ['exception' => $e2]);
        $mockLog->shouldReceive('error')->atLeast()->once()->ordered('logcalls')->with('Exception : error', ['exception' => $e1]);

        $sut = new LogException();
        $sut->setLogger($mockLog);
        $sut->logException($e1);
    }

    public function testInvoke(): void
    {
        $mockLog = m::mock(LoggerInterface::class);

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('Logger')->andReturn($mockLog);

        $sut = new LogException();
        $service = $sut->__invoke($mockSl, LogException::class);

        $this->assertSame($sut, $service);
    }
}
