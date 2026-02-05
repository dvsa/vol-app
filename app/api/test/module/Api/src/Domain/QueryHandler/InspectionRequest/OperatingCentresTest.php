<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\InspectionRequest;

use Dvsa\Olcs\Api\Domain\QueryHandler\InspectionRequest\OperatingCentres as QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\InspectionRequest as InspectionRequestRepo;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Transfer\Query\InspectionRequest\OperatingCentres as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * Operating Centres Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class OperatingCentresTest extends QueryHandlerTestCase
{
    public const ID = 9999;

    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('InspectionRequest', InspectionRequestRepo::class);
        $this->mockRepo('Application', ApplicationRepo::class);
        $this->mockRepo('Licence', LicenceRepo::class);

        parent::setUp();
    }

    public function testHandleQueryForLicence(): void
    {
        $query = Query::create(['type' => 'licence', 'identifier' => self::ID]);

        $mockOperCentreEntity = m::mock(Entity\Licence\LicenceOperatingCentre::class)
            ->shouldReceive('serialize')
            ->with(['address'])
            ->times(3)
            ->andReturn('SERIALIZED')
            ->getMock();

        $mockLicence = m::mock()
            ->shouldReceive('getOcForInspectionRequest')
            ->once()
            ->andReturn([$mockOperCentreEntity, $mockOperCentreEntity, $mockOperCentreEntity])
            ->getMock();

        $this->repoMap['Licence']
            ->shouldReceive('fetchWithOperatingCentres')
            ->with(self::ID)
            ->once()
            ->andReturn($mockLicence)
            ->getMock();

        static::assertEquals(
            [
                'result' => ['SERIALIZED', 'SERIALIZED', 'SERIALIZED'],
                'count' => 3,
            ],
            $this->sut->handleQuery($query)
        );
    }

    public function testHandleQueryForApplication(): void
    {
        $query = Query::create(['type' => 'application', 'identifier' => self::ID]);

        $mockOperCentreEntity = m::mock(Entity\Application\ApplicationOperatingCentre::class)
            ->shouldReceive('serialize')
            ->times(2)
            ->andReturn('SERIALIZED')
            ->getMock();

        $mockLicence = m::mock()
            ->shouldReceive('getOcForInspectionRequest')
            ->once()
            ->andReturn([$mockOperCentreEntity, $mockOperCentreEntity])
            ->getMock();

        $this->repoMap['Application']
            ->shouldReceive('fetchWithLicenceAndOc')
            ->with(self::ID)
            ->andReturn($mockLicence)
            ->once()
            ->getMock();

        static::assertEquals(
            [
                'result' => ['SERIALIZED', 'SERIALIZED'],
                'count' => 2,
            ],
            $this->sut->handleQuery($query)
        );
    }
}
