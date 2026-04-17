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
class PublicationTest extends RepositoryTestCase
{
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
        $mockQb->shouldReceive('expr->eq')->with('m.trafficArea', ':trafficArea')->once()->andReturnSelf();
        $mockQb->shouldReceive('andWhere')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('trafficArea', $trafficArea)->once()->andReturnSelf();
        $mockQb->shouldReceive('expr->eq')->with('m.pubType', ':pubType')->once()->andReturnSelf();
        $mockQb->shouldReceive('andWhere')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('pubType', $pubType)->once()->andReturnSelf();
        $mockQb->shouldReceive('expr->eq')->with('m.pubStatus', ':pubStatus')->once()->andReturnSelf();
        $mockQb->shouldReceive('andWhere')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')
            ->with('pubStatus', PublicationEntity::PUB_NEW_STATUS)
            ->once()
            ->andReturnSelf();
        $mockQb->shouldReceive('getQuery->getResult')
            ->with(Query::HYDRATE_OBJECT)
            ->andReturn($results);

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->once()
            ->with($mockQb)->andReturnSelf();

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
        $mockQb->shouldReceive('expr->in')->with('m.pubStatus', ':pubStatus')->once()->andReturnSelf();
        $mockQb->shouldReceive('andWhere')->once()->andReturnSelf();
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
        $mockQb->shouldReceive('expr->eq')
            ->with('m.pubStatus', ':pubStatus')
            ->once()
            ->andReturn('DUMMY_WHERE_PUB_STATUS');

        $mockQb->shouldReceive('andWhere')
            ->with('DUMMY_WHERE_PUB_STATUS')
            ->once()
            ->andReturnSelf();

        $mockQb->shouldReceive('setParameter')
            ->with('pubStatus', $status)
            ->once()
            ->andReturnSelf();

        $mockQb->shouldReceive('expr->gte')
            ->with('m.pubDate', ':pubDateFrom')
            ->once()
            ->andReturn('DUMMY_WHERE_PUB_DATE_FROM');

        $mockQb->shouldReceive('andWhere')
            ->with('DUMMY_WHERE_PUB_DATE_FROM')
            ->once()
            ->andReturnSelf();

        $mockQb->shouldReceive('setParameter')
            ->with('pubDateFrom', 'DUMMY_PUB_DATE_FROM')
            ->once()
            ->andReturnSelf();

        $mockQb->shouldReceive('expr->lt')
            ->with('m.pubDate', ':pubDateTo')
            ->once()
            ->andReturn('DUMMY_WHERE_PUB_DATE_TO');

        $mockQb->shouldReceive('andWhere')
            ->with('DUMMY_WHERE_PUB_DATE_TO')
            ->once()
            ->andReturnSelf();

        $mockQb->shouldReceive('setParameter')
            ->with('pubDateTo', 'DUMMY_PUB_DATE_TO')
            ->once()
            ->andReturnSelf();

        if ($withPubType) {
            $mockQb->shouldReceive('expr->eq')
                ->with('m.pubType', ':pubType')
                ->once()
                ->andReturn('DUMMY_WHERE_PUB_TYPE');

            $mockQb->shouldReceive('andWhere')
                ->with('DUMMY_WHERE_PUB_TYPE')
                ->once()
                ->andReturnSelf();

            $mockQb->shouldReceive('setParameter')
                ->with('pubType', $withPubType)
                ->once()
                ->andReturnSelf();
        }

        if ($withTrafficArea) {
            $mockQb->shouldReceive('expr->eq')
                ->with('m.trafficArea', ':trafficArea')
                ->once()
                ->andReturn('DUMMY_WHERE_TRAFFIC_AREA');

            $mockQb->shouldReceive('andWhere')
                ->with('DUMMY_WHERE_TRAFFIC_AREA')
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

    public static function providePublishedListCases(): array
    {
        return [
            [false, true],
            [true, true],
            [false, false],
            [true, false],
        ];
    }
}
