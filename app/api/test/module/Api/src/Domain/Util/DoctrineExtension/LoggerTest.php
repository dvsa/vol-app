<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Util\DoctrineExtension;

use Olcs\Logging\Log\Logger;
use Olcs\Logging\Test\RecordingLogger;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;

final class LoggerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Restores the static Logger facade. Without this, the mock this test installs stays
     * installed for whatever test runs next, which then fails on log calls it never made.
     */
    protected function tearDown(): void
    {
        Logger::setLogger(new NullLogger());

        parent::tearDown();
    }

    public function testStopQuery(): void
    {
        $recorder = new RecordingLogger();
        Logger::setLogger($recorder);

        $sut = new \Dvsa\Olcs\Api\Domain\Util\DoctrineExtension\Logger();

        $sut->startQuery('SELECT * FROM FOO', ['params' => 1], ['types' => 2]);
        $sut->stopQuery();

        $record = $recorder->last();

        $this->assertNotNull($record);
        $this->assertSame(LogLevel::DEBUG, $record['level']);
        $this->assertSame('SQL Query', $record['message']);
        $this->assertSame('SELECT * FROM FOO', $record['context']['query']['sql']);
    }
}
