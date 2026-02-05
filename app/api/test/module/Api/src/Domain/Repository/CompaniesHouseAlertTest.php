<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Query\CompaniesHouse\AlertList as AlertListQry;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\Repository\CompaniesHouseAlert::class)]
class CompaniesHouseAlertTest extends RepositoryTestCase
{
    /** @var  Repository\CompaniesHouseAlert | m\MockInterface */
    protected $sut;

    public function setUp(): void
    {
        $this->setUpSut(Repository\CompaniesHouseAlert::class, true);
    }

    public function testFetchListDefault(): void
    {
        $mockQb = $this->createMockQb('{QUERY}');
        $this->mockCreateQueryBuilder($mockQb);

        $data = [
            'includeClosed' => '',
            'typeOfChange' => '',
            'trafficAreas' => ['A', 'B'],
        ];

        $query = AlertListQry::create($data);

        $this->sut->shouldReceive('fetchPaginatedList')
            ->once()
            ->with($mockQb, Query::HYDRATE_ARRAY)
            ->andReturn(['foo' => 'bar'])
            ->shouldReceive('buildDefaultListQuery')
            ->once();

        $this->assertEquals(['foo' => 'bar'], $this->sut->fetchList($query));

        $expected = '{QUERY} SELECT cha_o, cha_o_ls, cha_o_lst INNER JOIN cha.organisation cha_o INNER JOIN cha_o.licences cha_o_ls WITH cha_o_ls.status IN ([[["lsts_curtailed","lsts_valid","lsts_suspended"]]]) INNER JOIN cha_o_ls.licenceType cha_o_lst AND cha.isClosed = 0 AND cha_o_ls.trafficArea IN [[["A","B"]]]';

        $this->assertEquals($expected, $this->query);
    }

    public function testFetchListIncludeClosedAndFilterType(): void
    {
        $mockQb = $this->createMockQb('{QUERY}');
        $this->mockCreateQueryBuilder($mockQb);

        $data = [
            'includeClosed' => 1,
            'typeOfChange' => 'some_type',
            'trafficAreas' => ['A', 'B'],
        ];

        $query = AlertListQry::create($data);

        $this->sut->shouldReceive('fetchPaginatedList')
            ->once()
            ->with($mockQb, Query::HYDRATE_ARRAY)
            ->andReturn(['foo' => 'bar'])
            ->shouldReceive('buildDefaultListQuery')
            ->once();

        $this->assertEquals(['foo' => 'bar'], $this->sut->fetchList($query));

        $expected = '{QUERY} SELECT cha_o, cha_o_ls, cha_o_lst INNER JOIN cha.organisation cha_o INNER JOIN cha_o.licences cha_o_ls WITH cha_o_ls.status IN ([[["lsts_curtailed","lsts_valid","lsts_suspended"]]]) INNER JOIN cha_o_ls.licenceType cha_o_lst INNER JOIN cha.reasons r WITH r.reasonType = [[some_type]] AND cha_o_ls.trafficArea IN [[["A","B"]]]';

        $this->assertEquals($expected, $this->query);
    }

    public function testGetReasonValueOptions(): void
    {
        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);

        $mockRefDataRepo = m::mock();

        $this->em
            ->shouldReceive('getRepository')
            ->with(RefData::class)
            ->andReturn($mockRefDataRepo);

        $mockRefDataRepo
            ->shouldReceive('createQueryBuilder')
            ->with('r')
            ->andReturn($qb);

        $where = m::mock();
        $qb
            ->shouldReceive('where')
            ->once()
            ->with($where)
            ->andReturnSelf();
        $qb
            ->shouldReceive('expr->eq')
            ->once()
            ->with('r.refDataCategoryId', ':CATEGORY_ID')
            ->andReturn($where);
        $qb
            ->shouldReceive('setParameter')
            ->once()
            ->with('CATEGORY_ID', 'ch_alert_reason')
            ->andReturnSelf();

        $result = [
            [
                'id' => 'reason_1',
                'description' => 'Reason 1',
            ],
            [
                'id' => 'reason_2',
                'description' => 'Reason 2',
            ],
        ];

        $qb
            ->shouldReceive('getQuery->getArrayResult')
            ->once()
            ->andReturn($result);

        $this->assertEquals(
            [
                'reason_1' => 'Reason 1',
                'reason_2' => 'Reason 2',
            ],
            $this->sut->getReasonValueOptions()
        );
    }
}
