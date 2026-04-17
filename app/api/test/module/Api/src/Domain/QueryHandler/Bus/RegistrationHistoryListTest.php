<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Bus;

use Dvsa\Olcs\Api\Domain\Query\Bus\ByLicenceRoute as LicenceRouteNoQuery;
use Dvsa\Olcs\Api\Domain\QueryHandler\Bus\RegistrationHistoryList;
use Dvsa\Olcs\Api\Domain\Repository\Bus as BusRepo;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusEntity;
use Dvsa\Olcs\Transfer\Query\Bus\RegistrationHistoryList as RegListQuery;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * RegistrationHistoryList Test
 */
class RegistrationHistoryListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = m::mock(RegistrationHistoryList::class)->makePartial()->shouldAllowMockingProtectedMethods();
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
        $mockBusReg->shouldReceive('getRouteNo')->andReturn($routeNo);
        $mockBusReg->shouldReceive('getLicence->getId')->andReturn(9999);

        $this->repoMap['Bus']->shouldReceive('fetchUsingId')
            ->with($regListQuery)
            ->andReturn($mockBusReg);

        $this->sut->shouldReceive('getQueryHandler->handleQuery')
            ->with(m::type(LicenceRouteNoQuery::class), false)
            ->andReturn(['foo']);

        $this->assertEquals(['foo'], $this->sut->handleQuery($regListQuery));
    }
}
