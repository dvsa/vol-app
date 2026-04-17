<?php

declare(strict_types=1);

/**
 * VenueTest
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\Venue as Repo;
use Mockery as m;

/**
 * Venue Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class VenueTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(Repo::class);
    }

    public function testApplyListFilters(): void
    {
        $this->setUpSut(Repo::class, true);

        $mockQb = m::mock(\Doctrine\ORM\QueryBuilder::class);

        $mockQ = m::mock(\Dvsa\Olcs\Transfer\Query\QueryInterface::class);
        $mockQ->shouldReceive('getTrafficArea')->andReturn('B');

        $mockQb->shouldReceive('expr->eq')->with('m.trafficArea', ':trafficArea')->once()->andReturn('EXPR');
        $mockQb->shouldReceive('andWhere')->with('EXPR')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('trafficArea', 'B')->once();

        $mockQb->shouldReceive('expr->isNull')->with('m.endDate')->once()->andReturn('C1');
        $mockQb->shouldReceive('expr->gt')->with('m.endDate', ':today')->once()->andReturn('C2');
        $mockQb->shouldReceive('expr->orX')->with('C1', 'C2')->once()->andReturn('C1C2');
        $mockQb->shouldReceive('andWhere')->with('C1C2')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('today', m::type(\DateTime::class))->once();

        $mockQb->shouldReceive('orderBy')->with('m.name', 'ASC')->once()->andReturnSelf();

        $this->sut->applyListFilters($mockQb, $mockQ);
    }
}
