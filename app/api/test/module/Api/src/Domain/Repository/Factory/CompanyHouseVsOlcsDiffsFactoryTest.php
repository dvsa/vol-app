<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository\Factory;

use Dvsa\Olcs\Api\Domain\Repository\CompaniesHouseVsOlcsDiffs;
use Dvsa\Olcs\Api\Domain\Repository\Factory\CompaniesHouseVsOlcsDiffsFactory;
use Psr\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers Dvsa\Olcs\Api\Domain\Repository\Factory\CompaniesHouseVsOlcsDiffsFactory
 */
class CompanyHouseVsOlcsDiffsFactoryTest extends MockeryTestCase
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
            CompaniesHouseVsOlcsDiffs::class,
            (new CompaniesHouseVsOlcsDiffsFactory())->__invoke($container, CompaniesHouseVsOlcsDiffs::class)
        );
    }
}
