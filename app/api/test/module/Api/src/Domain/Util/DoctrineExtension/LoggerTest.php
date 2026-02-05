<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Util\DoctrineExtension;

use Olcs\Logging\Log\Logger;

/**
 * Class LoggerTest
 * @package Dvsa\OlcsTest\Api\Domain\Util\DoctrineExtension
 */
class LoggerTest extends \PHPUnit\Framework\TestCase
{
    public function testStopQuery(): void
    {
        $logger = new \Dvsa\OlcsTest\SafeLogger();
        $logger->addWriter(new \Laminas\Log\Writer\Mock());
        Logger::setLogger($logger);

        $sut = new \Dvsa\Olcs\Api\Domain\Util\DoctrineExtension\Logger();

        $sut->startQuery('SELECT * FROM FOO', ['params' => 1], ['types' => 2]);
        $sut->stopQuery();

        $logger = \Olcs\Logging\Log\Logger::getLogger();
        $events = $logger->getWriters()->current()->events;

        $event = end($events);
        $this->assertSame(7, $event['priority']);
        $this->assertSame('SQL Query', $event['message']);
        $this->assertSame('SELECT * FROM FOO', $event['extra']['query']['sql']);
    }
}
