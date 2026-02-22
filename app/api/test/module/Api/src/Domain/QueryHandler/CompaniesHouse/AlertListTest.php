<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\CompaniesHouse;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\CompaniesHouse\AlertList;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Transfer\Query\CompaniesHouse\AlertList as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\QueryHandler\CompaniesHouse\AlertList::class)]
class AlertListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new AlertList();
        $this->mockRepo('CompaniesHouseAlert', Repository\CompaniesHouseAlert::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create([]);
        $mockOrganisation = m::mock(Organisation::class);
        $mockLicence = m::mock(Licence::class);
        $mockLicence->shouldReceive('serialize')->andReturn(['licNo' => 'test']);
        $mockOrganisation->shouldReceive('getLicences')->andReturn([$mockLicence])->getMock();
        $mockOrganisation->shouldReceive('serialize')->andReturn(['name' => 'test']);

        $alert1 = m::mock()->shouldReceive('serialize')->andReturn(['id' => 1])->getMock();
        $alert2 = m::mock()->shouldReceive('serialize')->andReturn(['id' => 2])->getMock();
        $mockList = [$alert1, $alert2];
        $alert1->shouldReceive('getOrganisation')
            ->andReturn($mockOrganisation);
        $alert2->shouldReceive('getOrganisation')
            ->andReturn($mockOrganisation);

        $userData = [
            'dataAccess' => [
                'trafficAreas' => ['A', 'B'],
            ],
        ];

        $this->expectedUserDataCacheCall($userData);

        $this->repoMap['CompaniesHouseAlert']
            ->shouldReceive('fetchList')
            ->with($query, Query::HYDRATE_OBJECT)
            ->once()
            ->andReturn($mockList)

            ->shouldReceive('fetchCount')
            ->with($query)
            ->andReturn(2)
            ->shouldReceive('getReasonValueOptions')
            ->once()
            ->andReturn(['foo' => 'bar']);

        $result = $this->sut->handleQuery($query);

        $expected = [
            'result' => [
                ['id' => 1,'licence' => ['licNo' => 'test'], 'organisation' => ['name' => 'test']],
                ['id' => 2, 'licence' => ['licNo' => 'test'], 'organisation' => ['name' => 'test']],
            ],
            'count' => 2,
            'valueOptions' => [
                'companiesHouseAlertReason' => [
                    'foo' => 'bar',
                ],
            ],
        ];

        $this->assertEquals($expected, $result);
    }
}
