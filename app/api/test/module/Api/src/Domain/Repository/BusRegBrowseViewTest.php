<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\BusRegBrowseView as Repo;
use Dvsa\Olcs\Api\Entity\View\BusRegBrowseView as Entity;
use Dvsa\Olcs\Transfer\Query\Bus\BusRegBrowseList as BusRegBrowseListQuery;

final class BusRegBrowseViewTest extends RepositoryTestCase
{
    private const string FROM = ' FROM ' . Entity::class . ' m';

    private const string COMMON_WHERE = ' WHERE m.acceptedDate = :byAcceptedDate'
        . ' AND m.trafficAreaId IN(:byTrafficAreas)';

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    public function testFetchDistinctList(): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchDistinctList('regNo'));

        $this->assertSame('SELECT DISTINCT m.regNo' . self::FROM, $qb->getDQL());
    }

    /**
     * The column names are prefixed with the alias in place before being selected, and the
     * date is normalised to Y-m-d whatever format it arrives in.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('exportProvider')]
    public function testFetchForExport(array $columns, ?string $status, string $expected): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('toIterable')->andReturn(['RESULTS']);

        $this->assertSame(
            ['RESULTS'],
            $this->sut->fetchForExport($columns, '2017-2-1', ['TA1', 'TA2'], $status),
        );

        $this->assertSame($expected, $qb->getDQL());
        $this->assertSame('2017-02-01', $qb->getParameter('byAcceptedDate')->getValue());
        $this->assertSame(['TA1', 'TA2'], $qb->getParameter('byTrafficAreas')->getValue());
    }

    public static function exportProvider(): \Iterator
    {
        yield 'with a status' => [
            ['regNo', 'variationNo'],
            'STATUS',
            'SELECT m.regNo, m.variationNo' . self::FROM . self::COMMON_WHERE
            . ' AND m.status = :byStatus',
        ];
        yield 'without a status' => [
            ['regNo'],
            null,
            'SELECT m.regNo' . self::FROM . self::COMMON_WHERE,
        ];
    }

    public function testFetchList(): void
    {
        $qb = $this->createRealQb();
        $this->sut->expects('fetchPaginatedList')->andReturn(['RESULTS']);

        $query = BusRegBrowseListQuery::create([
            'trafficAreas' => ['TA1', 'TA2'],
            'status' => 'STATUS',
            'acceptedDate' => '2017-02-01',
        ]);

        $this->assertSame(['RESULTS'], $this->sut->fetchList($query));

        $this->assertSame(
            'SELECT m' . self::FROM . self::COMMON_WHERE . ' AND m.status = :byStatus',
            $qb->getDQL(),
        );
        $this->assertSame('STATUS', $qb->getParameter('byStatus')->getValue());
    }
}
