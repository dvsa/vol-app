<?php

namespace OlcsTest\Logging\Log\Processor;

use DateTimeImmutable;
use Laminas\Session\Container;
use Laminas\Session\ManagerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Monolog\Level;
use Monolog\LogRecord;
use Olcs\Logging\Log\Processor\SessionId;

class SessionIdTest extends TestCase
{
    public function testGetSessionManager(): void
    {
        $mockSessionManager = m::mock(ManagerInterface::class);
        Container::setDefaultManager($mockSessionManager);

        $sut = new SessionId();
        $manager = $sut->getSessionManager();

        $this->assertSame($mockSessionManager, $manager);

        Container::setDefaultManager(null);
    }

    public function testProcess(): void
    {
        $sessionId = 'ghastsdrf';

        $mockSessionManager = m::mock(ManagerInterface::class);
        $mockSessionManager->shouldReceive('start');
        $mockSessionManager->shouldReceive('getId')->andReturn($sessionId);

        $sut = new SessionId();
        $sut->setSessionManager($mockSessionManager);

        $result = $sut(new LogRecord(new DateTimeImmutable(), 'test', Level::Info, ''));

        $this->assertArrayHasKey('sessionId', $result->extra);
        $this->assertSame($sessionId, $result->extra['sessionId']);
    }
}
