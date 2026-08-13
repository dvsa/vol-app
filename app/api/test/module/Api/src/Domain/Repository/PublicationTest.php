<?php

declare(strict_types=1);

/**
 * Publication test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\Repository\Publication;
use Dvsa\Olcs\Api\Entity\Publication\Publication as PublicationEntity;
use Dvsa\Olcs\Transfer\Query\Publication\PendingList;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\Publication as PublicationRepo;
use Doctrine\ORM\EntityRepository;

/**
 * Publication test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 *
 * @property Publication|m\Mock $sut
 */
final class PublicationTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpSut(PublicationRepo::class, true);
    }

    /**
     * @param $qb
     *
     * @return m\MockInterface
     */
    public function getMockRepo(mixed $qb): mixed
    {
        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('createQueryBuilder')
            ->with('m')
            ->andReturn($qb);

        return $repo;
    }

    /**
     * Tests fetch latest publication for traffic area and type
     */
    public function testFetchLatestForTrafficAreaAndType(): void
    {
        $trafficArea = 'M';
        $pubType = 'A&D';
        $results = [0 => m::mock(PublicationEntity::class)];

        $mockQb = $this->getMockTaAndTypeQb($trafficArea, $pubType, $results);

        /** @var EntityRepository $repo */
        $repo = $this->getMockRepo($mockQb);

        $this->em->shouldReceive('getRepository')
            ->with(PublicationEntity::class)
            ->andReturn($repo);

        $this->sut->fetchLatestForTrafficAreaAndType($trafficArea, $pubType);
    }

    public function testFetchLatestForTrafficAreaAndTypeNotFound(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\NotFoundException::class);

        $trafficArea = 'M';
        $pubType = 'A&D';
        $results = [];

        $mockQb = $this->getMockTaAndTypeQb($trafficArea, $pubType, $results);

        /** @var EntityRepository $repo */
        $repo = $this->getMockRepo($mockQb);

        $this->em->shouldReceive('getRepository')
            ->with(PublicationEntity::class)
            ->andReturn($repo);

        $this->sut->fetchLatestForTrafficAreaAndType($trafficArea, $pubType);
    }

    /**
     * @param string $trafficArea
     * @param string $pubType
     * @param array  $results
     *
     * @return m\MockInterface
     */
    public function getMockTaAndTypeQb(mixed $trafficArea, mixed $pubType, mixed $results): m\MockInterface
{
    $mockQb = m::mock(QueryBuilder::class);
    $expr = m::mock(\Doctrine\ORM\Query\Expr::class);

    $trafficAreaComparison = m::mock(
        \Doctrine\ORM\Query\Expr\Comparison::class
    );
    $pubTypeComparison = m::mock(
        \Doctrine\ORM\Query\Expr\Comparison::class
    );
    $pubStatusComparison = m::mock(
        \Doctrine\ORM\Query\Expr\Comparison::class
    );

    $mockQb->shouldReceive('expr')
        ->andReturn($expr);

    $expr->shouldReceive('eq')
        ->with('m.trafficArea', ':trafficArea')
        ->once()
        ->andReturn($trafficAreaComparison);

    $mockQb->shouldReceive('andWhere')
        ->with($trafficAreaComparison)
        ->once()
        ->andReturnSelf();

    $mockQb->shouldReceive('setParameter')
        ->with('trafficArea', $trafficArea)
        ->once()
        ->andReturnSelf();

    $expr->shouldReceive('eq')
        ->with('m.pubType', ':pubType')
        ->once()
        ->andReturn($pubTypeComparison);

    $mockQb->shouldReceive('andWhere')
        ->with($pubTypeComparison)
        ->once()
        ->andReturnSelf();

    $mockQb->shouldReceive('setParameter')
        ->with('pubType', $pubType)
        ->once()
        ->andReturnSelf();

    $expr->shouldReceive('eq')
        ->with('m.pubStatus', ':pubStatus')
        ->once()
        ->andReturn($pubStatusComparison);

    $mockQb->shouldReceive('andWhere')
        ->with($pubStatusComparison)
        ->once()
        ->andReturnSelf();

    $mockQb->shouldReceive('setParameter')
        ->with('pubStatus', PublicationEntity::PUB_NEW_STATUS)
        ->once()
        ->andReturnSelf();

    $mockQb->shouldReceive('getQuery->getResult')
        ->with(Query::HYDRATE_OBJECT)
        ->andReturn($results);

    $this->queryBuilder->shouldReceive('modifyQuery')
        ->once()
        ->with($mockQb)
        ->andReturnSelf();

    return $mockQb;
}

    /**
     * tests fetchPendingList
     */
    public function testFetchPendingList(): void
    {
        /** @var PendingList|m\Mock $query */
        $query = m::mock(PendingList::class);

        $count = 1;
        $results = [0 => m::mock(PublicationEntity::class)];
        $resultArray = [
            'results' => $results,
            'count' => $count
        ];

        $mockQb = m::mock(QueryBuilder::class);
        $expr = m::mock(\Doctrine\ORM\Query\Expr::class);
        $pubStatus = m::mock(\Doctrine\ORM\Query\Expr\Func::class);

        $mockQb->shouldReceive('expr')
            ->andReturn($expr);

        $expr->shouldReceive('in')
            ->with('m.pubStatus', ':pubStatus')
            ->once()
            ->andReturn($pubStatus);

        $mockQb->shouldReceive('andWhere')
            ->with($pubStatus)
            ->once()
            ->andReturnSelf();
        $mockQb->shouldReceive('setParameter')
            ->with('pubStatus', [PublicationEntity::PUB_NEW_STATUS, PublicationEntity::PUB_GENERATED_STATUS])
            ->once()
            ->andReturnSelf();

        $mockQb->shouldReceive('getQuery->getResult')
            ->andReturn($results);

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->once()
            ->with($mockQb)->andReturnSelf();

        /** @var EntityRepository $repo */
        $repo = $this->getMockRepo($mockQb);

        $this->sut->shouldReceive('buildDefaultListQuery')->once()->with($mockQb, $query)->andReturnSelf();

        $this->sut->shouldReceive('fetchPaginatedCount')
            ->once()
            ->with($mockQb)
            ->andReturn($count);

        $this->em->shouldReceive('getRepository')
            ->with(PublicationEntity::class)
            ->andReturn($repo);

        $this->assertEquals($resultArray, $this->sut->fetchPendingList($query));
    }

    /**
     *
     * @param $withPubType
     * @param $withTrafficArea
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('providePublishedListCases')]
    public function testFetchPublishedList(mixed $withPubType, mixed $withTrafficArea): void
{
    /** @var QueryInterface|m\Mock $query */
    $query = m::mock(QueryInterface::class);

    $count = 1;
    $results = [0 => m::mock(PublicationEntity::class)];
    $resultArray = [
        'results' => $results,
        'count' => $count
    ];
    $status = PublicationEntity::PUB_PRINTED_STATUS;

    $mockQb = m::mock(QueryBuilder::class);
    $expr = m::mock(\Doctrine\ORM\Query\Expr::class);

    $mockQb->shouldReceive('expr')
        ->andReturn($expr);

    $pubStatus = m::mock(
        \Doctrine\ORM\Query\Expr\Comparison::class
    );
    $expr->shouldReceive('eq')
        ->with('m.pubStatus', ':pubStatus')
        ->once()
        ->andReturn($pubStatus);

    $mockQb->shouldReceive('andWhere')
        ->with($pubStatus)
        ->once()
        ->andReturnSelf();

    $mockQb->shouldReceive('setParameter')
        ->with('pubStatus', $status)
        ->once()
        ->andReturnSelf();

    $dateFrom = m::mock(
        \Doctrine\ORM\Query\Expr\Comparison::class
    );
    $expr->shouldReceive('gte')
        ->with('m.pubDate', ':pubDateFrom')
        ->once()
        ->andReturn($dateFrom);

    $mockQb->shouldReceive('andWhere')
        ->with($dateFrom)
        ->once()
        ->andReturnSelf();

    $mockQb->shouldReceive('setParameter')
        ->with('pubDateFrom', 'DUMMY_PUB_DATE_FROM')
        ->once()
        ->andReturnSelf();

    $dateTo = m::mock(
        \Doctrine\ORM\Query\Expr\Comparison::class
    );
    $expr->shouldReceive('lt')
        ->with('m.pubDate', ':pubDateTo')
        ->once()
        ->andReturn($dateTo);

    $mockQb->shouldReceive('andWhere')
        ->with($dateTo)
        ->once()
        ->andReturnSelf();

    $mockQb->shouldReceive('setParameter')
        ->with('pubDateTo', 'DUMMY_PUB_DATE_TO')
        ->once()
        ->andReturnSelf();

    if ($withPubType) {
        $pubType = m::mock(
            \Doctrine\ORM\Query\Expr\Comparison::class
        );

        $expr->shouldReceive('eq')
            ->with('m.pubType', ':pubType')
            ->once()
            ->andReturn($pubType);

        $mockQb->shouldReceive('andWhere')
            ->with($pubType)
            ->once()
            ->andReturnSelf();

        $mockQb->shouldReceive('setParameter')
            ->with('pubType', $withPubType)
            ->once()
            ->andReturnSelf();
    }

    if ($withTrafficArea) {
        $trafficArea = m::mock(
            \Doctrine\ORM\Query\Expr\Comparison::class
        );

        $expr->shouldReceive('eq')
            ->with('m.trafficArea', ':trafficArea')
            ->once()
            ->andReturn($trafficArea);

        $mockQb->shouldReceive('andWhere')
            ->with($trafficArea)
            ->once()
            ->andReturnSelf();

        $mockQb->shouldReceive('setParameter')
            ->with('trafficArea', 'DUMMY_TRAFFIC_AREA')
            ->once()
            ->andReturnSelf();
    }

    $mockQb->shouldReceive('getQuery->getResult')
        ->andReturn($results);

    $this->queryBuilder->shouldReceive('modifyQuery')
        ->once()
        ->with($mockQb)
        ->andReturnSelf();

    /** @var EntityRepository $repo */
    $repo = $this->getMockRepo($mockQb);

    $this->sut->shouldReceive('buildDefaultListQuery')
        ->once()
        ->with($mockQb, $query)
        ->andReturnSelf();

    $this->sut->shouldReceive('fetchPaginatedCount')
        ->once()
        ->with($mockQb)
        ->andReturn($count);

    $this->em->shouldReceive('getRepository')
        ->with(PublicationEntity::class)
        ->andReturn($repo);

    $this->assertEquals(
        $resultArray,
        $this->sut->fetchPublishedList(
            $query,
            $withPubType ? 'DUMMY_PUB_TYPE' : '',
            'DUMMY_PUB_DATE_FROM',
            'DUMMY_PUB_DATE_TO',
            $withTrafficArea ? 'DUMMY_TRAFFIC_AREA' : ''
        )
    );
}

    public static function providePublishedListCases(): \Iterator
    {
        yield [false, true];
        yield [true, true];
        yield [false, false];
        yield [true, false];
    }
}
