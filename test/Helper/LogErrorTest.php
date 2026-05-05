<?php

namespace OlcsTest\Logging\Helper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Logging\Helper\LogError;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class LogErrorTest extends TestCase
{
    public function testInvoke(): void
    {
        $mockLog = m::mock(LoggerInterface::class);

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('Logger')->andReturn($mockLog);

        $sut = new LogError();
        $service = $sut->__invoke($mockSl, LogError::class);

        $this->assertSame($sut, $service);
    }
}
