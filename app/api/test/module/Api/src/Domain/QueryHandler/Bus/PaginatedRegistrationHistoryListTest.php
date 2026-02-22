<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Bus;

use Dvsa\Olcs\Api\Domain\Query\Bus\ByLicenceRoute as LicenceRouteNoQuery;
use Dvsa\Olcs\Api\Domain\QueryHandler\Bus\PaginatedRegistrationHistoryList;
use Dvsa\Olcs\Api\Domain\Repository\Bus as BusRepo;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Transfer\Query\Bus\PaginatedRegistrationHistoryList as RegListQuery;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * PaginatedRegistrationHistoryList Test
 */
class PaginatedRegistrationHistoryListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = m::mock(PaginatedRegistrationHistoryList::class)
            ->makePartial()->shouldAllowMockingProtectedMethods();
        $this->mockRepo('Bus', BusRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $busRegId = 99;
        $routeNo = 12345;
        $sort = 'sort';
        $order = 'DESC';
        $page = 1;
        $limit = 10;

        $regListQueryParams = [
            'id' => $busRegId,
            'sort' => $sort,
            'order' => $order,
            'page' => $page,
            'limit' => $limit
        ];

        $regListQuery = RegListQuery::create($regListQueryParams);

        $mockBusReg = m::mock(BusEntity::class);
        $mockLicence = m::mock(LicenceEntity::class)->makePartial();
        $mockLicence->setId(9999);

        $mockBusReg->shouldReceive('getRouteNo')->andReturn($routeNo);
        $mockBusReg->shouldReceive('getLicence')->andReturn($mockLicence);

        $this->repoMap['Bus']->shouldReceive('fetchUsingId')
            ->with($regListQuery)
            ->andReturn($mockBusReg);

        $this->sut->shouldReceive('getQueryHandler->handleQuery')
            ->with(m::type(LicenceRouteNoQuery::class), false)
            ->andReturn(['foo']);

        $this->assertEquals(['foo'], $this->sut->handleQuery($regListQuery));
    }
}
