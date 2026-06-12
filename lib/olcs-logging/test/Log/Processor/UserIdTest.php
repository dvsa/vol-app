<?php

namespace OlcsTest\Logging\Log\Processor;

use DateTimeImmutable;
use Monolog\Level;
use Monolog\LogRecord;
use Olcs\Logging\Log\Processor\UserId;
use PHPUnit\Framework\TestCase;

class UserIdTest extends TestCase
{
    public function testProcessNoUser(): void
    {
        $sut = new UserId();
        $sut->setUserId(null);

        $result = $sut(new LogRecord(new DateTimeImmutable(), 'test', Level::Info, ''));

        $this->assertArrayHasKey('userId', $result->extra);
        $this->assertNull($result->extra['userId']);
    }

    public function testProcessWithUserId(): void
    {
        $sut = new UserId();
        $sut->setUserId('USER123');

        $result = $sut(new LogRecord(new DateTimeImmutable(), 'test', Level::Info, ''));

        $this->assertSame('USER123', $result->extra['userId']);
    }
}
