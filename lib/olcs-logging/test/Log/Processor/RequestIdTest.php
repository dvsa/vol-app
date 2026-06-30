<?php

namespace OlcsTest\Logging\Log\Processor;

use DateTimeImmutable;
use Monolog\Level;
use Monolog\LogRecord;
use Olcs\Logging\Log\Processor\RequestId;
use PHPUnit\Framework\TestCase;

class RequestIdTest extends TestCase
{
    public function testProcess(): void
    {
        $sut = new RequestId();

        $result = $sut(new LogRecord(new DateTimeImmutable(), 'test', Level::Info, ''));

        $this->assertNotNull($result->extra['requestId']);
        $this->assertNotEmpty($result->extra['requestId']);
    }

    public function testIdentifierIsStableAcrossCalls(): void
    {
        $sut = new RequestId();

        $first = $sut(new LogRecord(new DateTimeImmutable(), 'test', Level::Info, ''));
        $second = $sut(new LogRecord(new DateTimeImmutable(), 'test', Level::Info, ''));

        $this->assertSame($first->extra['requestId'], $second->extra['requestId']);
    }

    public function testGetIdentifierUsesUniqueId(): void
    {
        $previous = $_SERVER['UNIQUE_ID'] ?? null;
        $_SERVER['UNIQUE_ID'] = 'request-marker';

        try {
            $sut = new RequestId();
            $this->assertSame(sha1('request-marker'), $sut->getIdentifier());
        } finally {
            if ($previous === null) {
                unset($_SERVER['UNIQUE_ID']);
            } else {
                $_SERVER['UNIQUE_ID'] = $previous;
            }
        }
    }
}
