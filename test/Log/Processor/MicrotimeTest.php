<?php

namespace OlcsTest\Logging\Log\Processor;

use DateTimeImmutable;
use Monolog\Level;
use Monolog\LogRecord;
use Olcs\Logging\Log\Processor\Microtime;
use PHPUnit\Framework\TestCase;

class MicrotimeTest extends TestCase
{
    public function testProcess(): void
    {
        $sut = new Microtime();
        $record = new LogRecord(new DateTimeImmutable(), 'test', Level::Info, '');

        $result = $sut($record);

        $this->assertArrayHasKey('microsecs', $result->extra);
        $this->assertSame(6, strlen($result->extra['microsecs']));
        $this->assertTrue(is_numeric($result->extra['microsecs']), 'Microsecs was not a number');
    }
}
