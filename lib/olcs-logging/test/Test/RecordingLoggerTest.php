<?php

namespace OlcsTest\Logging\Test;

use Olcs\Logging\Test\RecordingLogger;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

class RecordingLoggerTest extends TestCase
{
    public function testCapturesLogCallsViaPsrInterface(): void
    {
        $sut = new RecordingLogger();

        $sut->info('hello', ['who' => 'world']);
        $sut->error('boom', ['exception' => 'thing']);

        $this->assertCount(2, $sut->records);
        $this->assertSame(LogLevel::INFO, $sut->records[0]['level']);
        $this->assertSame('hello', $sut->records[0]['message']);
        $this->assertSame(['who' => 'world'], $sut->records[0]['context']);
        $this->assertSame(LogLevel::ERROR, $sut->records[1]['level']);
    }

    public function testHasRecord(): void
    {
        $sut = new RecordingLogger();
        $sut->debug('one');
        $sut->warning('two');

        $this->assertTrue($sut->hasRecord(LogLevel::DEBUG, 'one'));
        $this->assertTrue($sut->hasRecord(LogLevel::WARNING, 'two'));
        $this->assertFalse($sut->hasRecord(LogLevel::ERROR, 'one'));
        $this->assertFalse($sut->hasRecord(LogLevel::DEBUG, 'missing'));
    }

    public function testHasRecordsAtLevel(): void
    {
        $sut = new RecordingLogger();
        $sut->debug('one');
        $sut->warning('two');

        $this->assertTrue($sut->hasRecordsAtLevel(LogLevel::DEBUG));
        $this->assertTrue($sut->hasRecordsAtLevel(LogLevel::WARNING));
        $this->assertFalse($sut->hasRecordsAtLevel(LogLevel::ERROR));
    }

    public function testFirstAndLast(): void
    {
        $sut = new RecordingLogger();
        $this->assertNull($sut->first());
        $this->assertNull($sut->last());

        $sut->info('a');
        $sut->error('b');
        $sut->debug('c');

        $this->assertSame('a', $sut->first()['message']);
        $this->assertSame('c', $sut->last()['message']);
    }

    public function testReset(): void
    {
        $sut = new RecordingLogger();
        $sut->info('keep me');

        $sut->reset();

        $this->assertSame([], $sut->records);
    }
}
