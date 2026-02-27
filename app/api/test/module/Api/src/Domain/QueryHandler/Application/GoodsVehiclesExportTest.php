<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Application;

use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\QueryHandler\Application\GoodsVehiclesExport;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\QueryHandler\Application\GoodsVehiclesExport::class)]
class GoodsVehiclesExportTest extends QueryHandlerTestCase
{
    public const ID = 1111;
    public const APP_ID = 8888;
    public const LICENCE_ID = 777;

    /** @var GoodsVehiclesExport|m\MockInterface */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = m::mock(GoodsVehiclesExport::class . '[getData]')
            ->shouldAllowMockingProtectedMethods();

        $this->mockRepo('Application', Repository\Application::class);
        $this->mockRepo('LicenceVehicle', Repository\LicenceVehicle::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Query\Application\GoodsVehiclesExport::create(['id' => self::ID]);

        /** @var QueryBuilder $mockListQb */
        $mockListQb = m::mock(QueryBuilder::class);

        $mockLicEntity = m::mock(Entity\Application\Application::class)
            ->shouldReceive('getId')->once()->andReturn(self::LICENCE_ID)
            ->getMock();

        $mockAppEntity = m::mock(Entity\Application\Application::class)
            ->shouldReceive('getId')->once()->andReturn(self::APP_ID)
            ->shouldReceive('getLicence')->once()->andReturn($mockLicEntity)
            ->getMock();

        $this->repoMap['Application']
            ->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($mockAppEntity);

        $this->repoMap['LicenceVehicle']
            ->shouldReceive('createPaginatedVehiclesDataForApplicationQuery')
            ->with($query, self::APP_ID, self::LICENCE_ID)
            ->andReturn($mockListQb);

        $this->sut
            ->shouldReceive('getData')
            ->once()
            ->with($mockListQb)
            ->andReturn('EXPECT_RESULT');

        //  call & check
        $actual = $this->sut->handleQuery($query);

        static::assertEquals('EXPECT_RESULT', $actual);
    }
}
