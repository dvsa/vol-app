<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository\Factory;

use Psr\Container\ContainerInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Factory\DataDvaNiFactory;
use Dvsa\Olcs\Api\Domain\Repository\DataDvaNi;
use Mockery as m;

/**
 * @covers  Dvsa\Olcs\Api\Domain\Repository\Factory\DataDvaNiFactory
 */
class DataDvaNiFactoryTest extends MockeryTestCase
{
    public function testInvoke(): void
    {
        $mockConn = m::mock(\Doctrine\DBAL\Connection::class)
            ->shouldReceive('close')
            ->getMock();

        $container = m::mock(ContainerInterface::class)
            ->shouldReceive('get')
            ->once()
            ->with('doctrine.connection.export')
            ->andReturn($mockConn)
            ->getMock();

        static::assertInstanceOf(
            DataDvaNi::class,
            (new DataDvaNiFactory())->__invoke($container, DataDvaNi::class)
        );
    }
}
