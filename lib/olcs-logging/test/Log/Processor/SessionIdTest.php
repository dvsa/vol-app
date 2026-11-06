<?php

declare(strict_types=1);

namespace OlcsTest\Logging\Log\Processor;

use DateTimeImmutable;
use Laminas\Session\Container;
use Laminas\Session\ManagerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Monolog\Level;
use Monolog\LogRecord;
use Olcs\Logging\Log\Processor\SessionId;

final class SessionIdTest extends TestCase
{
    private const string SESSION_ID = '4a231c15f8f797c6d5f581609f6d99bb';

    public function testGetSessionManager(): void
    {
        $mockSessionManager = m::mock(ManagerInterface::class);
        Container::setDefaultManager($mockSessionManager);

        $sut = new SessionId();
        $manager = $sut->getSessionManager();

        $this->assertSame($mockSessionManager, $manager);

        Container::setDefaultManager(null);
    }

    public function testAStartedSessionIsLoggedAsAFingerprintNeverItsId(): void
    {
        $mockSessionManager = m::mock(ManagerInterface::class);
        $mockSessionManager->expects('sessionExists')->withNoArgs()->andReturn(true);
        $mockSessionManager->expects('getId')->withNoArgs()->andReturn(self::SESSION_ID);
        $mockSessionManager->shouldNotReceive('start');

        $sut = new SessionId();
        $sut->setSessionManager($mockSessionManager);

        $result = $sut($this->record());

        // The first 16 hex characters of SHA-256 of the id
        $this->assertSame('81ba51db3978b9d8', $result->extra['sessionId']);
        $this->assertSame(SessionId::fingerprint(self::SESSION_ID), $result->extra['sessionId']);
        $this->assertStringNotContainsString(self::SESSION_ID, json_encode($result->extra, JSON_THROW_ON_ERROR));
    }

    public function testNoSessionIsStartedToLogOne(): void
    {
        $mockSessionManager = m::mock(ManagerInterface::class);
        $mockSessionManager->expects('sessionExists')->withNoArgs()->andReturn(false);
        $mockSessionManager->shouldNotReceive('start');
        $mockSessionManager->shouldNotReceive('getId');

        $sut = new SessionId();
        $sut->setSessionManager($mockSessionManager);

        $record = $this->record();

        $this->assertSame($record, $sut($record));
    }

    public function testASessionWithNoIdIsNotLogged(): void
    {
        $mockSessionManager = m::mock(ManagerInterface::class);
        $mockSessionManager->expects('sessionExists')->withNoArgs()->andReturn(true);
        $mockSessionManager->expects('getId')->withNoArgs()->andReturn('');

        $sut = new SessionId();
        $sut->setSessionManager($mockSessionManager);

        $this->assertArrayNotHasKey('sessionId', $sut($this->record())->extra);
    }

    private function record(): LogRecord
    {
        return new LogRecord(new DateTimeImmutable(), 'test', Level::Info, '');
    }
}
