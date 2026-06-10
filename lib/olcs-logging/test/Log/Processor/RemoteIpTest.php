<?php

namespace OlcsTest\Logging\Log\Processor;

use DateTimeImmutable;
use Laminas\Http\PhpEnvironment\RemoteAddress;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Monolog\Level;
use Monolog\LogRecord;
use Olcs\Logging\Log\Processor\RemoteIp;

class RemoteIpTest extends TestCase
{
    public function testGetRemoteAddress(): void
    {
        $sut = new RemoteIp();
        $this->assertInstanceOf(RemoteAddress::class, $sut->getRemoteAddress());
    }

    public function testProcess(): void
    {
        $ip = '192.168.1.54';

        $mockRemoteAddr = m::mock(RemoteAddress::class);
        $mockRemoteAddr->shouldReceive('getIpAddress')->andReturn($ip);

        $sut = new RemoteIp();
        $sut->setRemoteAddress($mockRemoteAddr);

        $result = $sut(new LogRecord(new DateTimeImmutable(), 'test', Level::Info, ''));

        $this->assertArrayHasKey('remoteIp', $result->extra);
        $this->assertSame($ip, $result->extra['remoteIp']);
    }
}
