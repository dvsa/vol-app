<?php

declare(strict_types=1);

/**
 * TrafficArea test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\TrafficArea as TrafficAreaRepo;

/**
 * TrafficArea test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TrafficAreaTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(TrafficAreaRepo::class);
    }

    public function testGetValueOptionsWhereAllowedTrafficAreasUnspecified(): void
    {
        $mockQb = $this->createMockQueryBuilder();

        $this->expectWhereNiOnly($mockQb);
        $this->expectOrderByName($mockQb);

        $mockQb->shouldReceive('getQuery->getResult')
            ->andReturn([$this->createMockTrafficArea('A', 'Area A'), $this->createMockTrafficArea('B', 'Area B')]);

        $valueOptions = $this->sut->getValueOptions();

        $this->assertEquals(
            [
                'A' => 'Area A',
                'B' => 'Area B',
            ],
            $valueOptions
        );
    }

    public function testGetValueOptionsWhereAllowedTrafficAreaGb(): void
    {
        $mockQb = $this->createMockQueryBuilder();

        $this->expectWhereNiOnly($mockQb);
        $this->expectOrderByName($mockQb);

        $mockQb->shouldReceive('getQuery->getResult')
            ->andReturn([$this->createMockTrafficArea('A', 'Area A'), $this->createMockTrafficArea('B', 'Area B')]);

        $valueOptions = $this->sut->getValueOptions(Organisation::ALLOWED_OPERATOR_LOCATION_GB);

        $this->assertEquals(
            [
                'A' => 'Area A',
                'B' => 'Area B',
            ],
            $valueOptions
        );
    }

    public function testGetValueOptionsWhereAllowedTrafficAreaNi(): void
    {
        $mockQb = $this->createMockQueryBuilder();

        $this->expectOrderByName($mockQb);

        $mockQb->shouldReceive('getQuery->getResult')
            ->andReturn([$this->createMockTrafficArea('A', 'Area A'), $this->createMockTrafficArea('B', 'Area B')]);

        $valueOptions = $this->sut->getValueOptions(Organisation::ALLOWED_OPERATOR_LOCATION_NI);

        $this->assertEquals(
            [
                'A' => 'Area A',
                'B' => 'Area B',
            ],
            $valueOptions
        );
    }

    public function testFetchListForNewApplication(): void
    {
        $mockQb = $this->createMockQueryBuilder();

        $mockQb->shouldReceive('expr->eq')->with('m.isNi', ':isNi')->andReturn('expr')->once();
        $mockQb->shouldReceive('andWhere')->with('expr')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('isNi', 0)->once()->andReturnSelf();

        $mockQb->shouldReceive('getQuery->getResult')->once()->andReturn('results')->getMock();

        $this->queryBuilder->shouldReceive('modifyQuery')->with($mockQb)->once();

        $this->assertEquals('results', $this->sut->fetchListForNewApplication('GB'));
    }


    /**
     * @return m\MockInterface|QueryBuilder
     */
    protected function createMockQueryBuilder(): m\MockInterface
    {
        /** @var QueryBuilder $qb */
        $mockQb = m::mock(QueryBuilder::class);

        $this->em
            ->shouldReceive('getRepository->createQueryBuilder')
            ->once()
            ->andReturn($mockQb);
        return $mockQb;
    }

    /**
     * @param m\MockInterface $mockQb
     */
    protected function expectWhereNiOnly(mixed $mockQb): void
    {
        $mockQb->shouldReceive('expr->eq')->with('m.isNi', ':isNi')->andReturn('DUMMY_WHERE_EXPR');
        $mockQb->shouldReceive('andWhere')->with('DUMMY_WHERE_EXPR')->once(1)->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('isNi', 0)->once(1)->andReturnSelf();
    }

    /**
     * @param m\MockInterface $mockQb
     */
    private function expectOrderByName(mixed $mockQb): void
    {
        $mockQb->shouldReceive('orderBy')->with('m.name')->atLeast(1)->andReturnSelf();
    }

    /**
     * @param $id
     * @param $name
     *
     * @return m\MockInterface|TrafficArea
     */
    protected function createMockTrafficArea(mixed $id, mixed $name): mixed
    {
        $ta = m::mock(TrafficArea::class);
        $ta->shouldReceive('getId')->andReturn($id);
        $ta->shouldReceive('getName')->andReturn($name);
        return $ta;
    }
}
