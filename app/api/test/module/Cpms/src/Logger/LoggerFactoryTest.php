<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cpms\Logger;

use Dvsa\Olcs\Cpms\Logger\LoggerFactory;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

class LoggerFactoryTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestCreateLogger')]
    public function testCreateLogger(mixed $dpData): void
    {
        $zendLogLevel = $dpData['zendLogLevel'];
        $logPath = '/var/tmp/backend.log';

        /** @var LoggerFactory */
        $sut = new LoggerFactory($logPath, $zendLogLevel);
        $logger = $sut->createLogger();

        $this->assertInstanceOf(Logger::class, $logger);
        $this->assertEquals('cpms_client_logger', $logger->getName());
        $this->assertEquals('/var/tmp/backend.log', $logger->getHandlers()[0]->getUrl());
        $this->assertEquals($dpData['expectedMonologLevel'], $logger->getHandlers()[0]->getLevel());
    }

    public static function dpTestCreateLogger(): array
    {
        return [

            [
                [
                    'zendLogLevel' => 0,
                    'expectedMonologLevel' => 600
                ]
            ],
            [
                [
                    'zendLogLevel' => 1,
                    'expectedMonologLevel' => 550
                ]
            ],
            [
                [
                    'zendLogLevel' => 2,
                    'expectedMonologLevel' => 500
                ]
            ],
            [
                [
                    'zendLogLevel' => 3,
                    'expectedMonologLevel' => 400
                ]
            ],
            [
                [
                    'zendLogLevel' => 4,
                    'expectedMonologLevel' => 300
                ]
            ],
            [
                [
                    'zendLogLevel' => 5,
                    'expectedMonologLevel' => 250
                ]
            ],
            [
                [
                    'zendLogLevel' => 6,
                    'expectedMonologLevel' => 200
                ]
            ],
            [
                [
                    'zendLogLevel' => 7,
                    'expectedMonologLevel' => 100
                ]
            ]
        ];
    }
}
