<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\CompaniesHouse\CompaniesHouseAlert as Entity;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Query\CompaniesHouse\AlertList as AlertListQry;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\Repository\CompaniesHouseAlert::class)]
final class CompaniesHouseAlertTest extends RepositoryTestCase
{
    protected $sut;

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repository\CompaniesHouseAlert::class, true);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('fetchListProvider')]
    public function testFetchList(array $data, string $expectedDql): void
    {
        $qb = $this->createRealQb();

        $this->sut->expects('fetchPaginatedList')
            ->with($qb, Query::HYDRATE_ARRAY)
            ->andReturn(['foo' => 'bar']);

        $this->assertSame(['foo' => 'bar'], $this->sut->fetchList(AlertListQry::create($data)));

        $this->assertSame($expectedDql, $qb->getDQL());
        $this->assertSame(
            [
                Licence::LICENCE_STATUS_CURTAILED,
                Licence::LICENCE_STATUS_VALID,
                Licence::LICENCE_STATUS_SUSPENDED,
            ],
            $qb->getParameter('licenceStatuses')->getValue(),
        );
        $this->assertSame(['A', 'B'], $qb->getParameter('trafficAreas')->getValue());
    }

    public static function fetchListProvider(): \Iterator
    {
        $joins = ' FROM ' . Entity::class . ' cha'
            . ' INNER JOIN cha.organisation cha_o'
            . ' INNER JOIN cha_o.licences cha_o_ls WITH cha_o_ls.status IN (:licenceStatuses)'
            . ' INNER JOIN cha_o_ls.licenceType cha_o_lst';

        // AlertList defaults to sorting by id; the old test stubbed buildDefaultListQuery
        // out entirely, so the default ordering was never exercised.
        $order = ' ORDER BY cha.id ASC';

        yield 'default excludes closed alerts' => [
            ['includeClosed' => '', 'typeOfChange' => '', 'trafficAreas' => ['A', 'B']],
            'SELECT cha, cha_o, cha_o_ls, cha_o_lst' . $joins
            . ' WHERE cha.isClosed = 0 AND cha_o_ls.trafficArea IN(:trafficAreas)'
            . $order,
        ];

        yield 'includeClosed drops the isClosed filter and adds the reason join' => [
            ['includeClosed' => 1, 'typeOfChange' => 'some_type', 'trafficAreas' => ['A', 'B']],
            'SELECT cha, cha_o, cha_o_ls, cha_o_lst' . $joins
            . ' INNER JOIN cha.reasons r WITH r.reasonType = :reasonType'
            . ' WHERE cha_o_ls.trafficArea IN(:trafficAreas)'
            . $order,
        ];
    }

    public function testGetReasonValueOptions(): void
    {
        $qb = $this->newRealQb();
        $qb->select('r')->from(RefData::class, 'r');
        $qb->stubbedQuery()->expects('getArrayResult')->andReturn([
            ['id' => 'reason_1', 'description' => 'Reason 1'],
            ['id' => 'reason_2', 'description' => 'Reason 2'],
        ]);
        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('r')->andReturn($qb);

        $this->assertSame(
            ['reason_1' => 'Reason 1', 'reason_2' => 'Reason 2'],
            $this->sut->getReasonValueOptions(),
        );

        $this->assertSame(
            'SELECT r FROM ' . RefData::class . ' r WHERE r.refDataCategoryId = :CATEGORY_ID',
            $qb->getDQL(),
        );
        $this->assertSame('ch_alert_reason', $qb->getParameter('CATEGORY_ID')->getValue());
    }
}
