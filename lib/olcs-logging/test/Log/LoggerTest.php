<?php

namespace OlcsTest\Logging\Log;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Logging\Log\Logger;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class LoggerTest extends MockeryTestCase
{
    private $logger;

    public function setUp(): void
    {
        $this->logger = m::mock(LoggerInterface::class);
        Logger::setLogger($this->logger);
        $this->assertSame($this->logger, Logger::getLogger());
    }

    public function testEmerg(): void
    {
        $this->logger->shouldReceive('emergency')->once()->with('foo', ['foo' => 'bar']);
        Logger::emerg('foo', ['foo' => 'bar']);
    }

    public function testAlert(): void
    {
        $this->logger->shouldReceive('alert')->once()->with('foo', ['foo' => 'bar']);
        Logger::alert('foo', ['foo' => 'bar']);
    }

    public function testCrit(): void
    {
        $this->logger->shouldReceive('critical')->once()->with('foo', ['foo' => 'bar']);
        Logger::crit('foo', ['foo' => 'bar']);
    }

    public function testErr(): void
    {
        $this->logger->shouldReceive('error')->once()->with('foo', ['foo' => 'bar']);
        Logger::err('foo', ['foo' => 'bar']);
    }

    public function testWarn(): void
    {
        $this->logger->shouldReceive('warning')->once()->with('foo', ['foo' => 'bar']);
        Logger::warn('foo', ['foo' => 'bar']);
    }

    public function testNotice(): void
    {
        $this->logger->shouldReceive('notice')->once()->with('foo', ['foo' => 'bar']);
        Logger::notice('foo', ['foo' => 'bar']);
    }

    public function testInfo(): void
    {
        $this->logger->shouldReceive('info')->once()->with('foo', ['foo' => 'bar']);
        Logger::info('foo', ['foo' => 'bar']);
    }

    public function testDebug(): void
    {
        $this->logger->shouldReceive('debug')->once()->with('foo', ['foo' => 'bar']);
        Logger::debug('foo', ['foo' => 'bar']);
    }

    public function testLog(): void
    {
        $this->logger->shouldReceive('log')->once()->with(LogLevel::ERROR, 'foo', ['foo' => 'bar']);
        Logger::log(LogLevel::ERROR, 'foo', ['foo' => 'bar']);
    }

    public function testLogResponseLogsAtDebug(): void
    {
        $this->logger->shouldReceive('debug')->once()->with('foo', ['foo' => 'bar']);
        Logger::logResponse(200, 'foo', ['foo' => 'bar']);
    }

    public function testLogException(): void
    {
        $e = new \Exception('Foo', 200);
        $message = "Code 200 : Foo\n" . $e->getFile() . ' Line ' . $e->getLine();

        $this->logger->shouldReceive('log')->once()->with(LogLevel::DEBUG, $message, ['trace' => $e->getTraceAsString()]);

        Logger::logException($e);
    }

    public function testLogExceptionWithExplicitPriority(): void
    {
        $e = new \Exception('Foo', 200);
        $message = "Code 200 : Foo\n" . $e->getFile() . ' Line ' . $e->getLine();

        $this->logger->shouldReceive('log')->once()->with(LogLevel::WARNING, $message, ['trace' => $e->getTraceAsString()]);

        Logger::logException($e, LogLevel::WARNING);
    }
}
