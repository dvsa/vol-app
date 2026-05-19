<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Util\DoctrineExtension;

use Olcs\Logging\Log\Logger;
use Olcs\Logging\Test\RecordingLogger;
use Psr\Log\LogLevel;

class LoggerTest extends \PHPUnit\Framework\TestCase
{
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
