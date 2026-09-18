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
final class VenueTest extends RepositoryTestCase
{
    #[\Override]
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

        $expr = new \Doctrine\ORM\Query\Expr();

        $mockQb->shouldReceive('expr')
            ->times(4)
            ->andReturn($expr);

        $mockQb->shouldReceive('andWhere')
            ->twice()
            ->andReturnSelf();

        $mockQb->shouldReceive('setParameter')->with('trafficArea', 'B')->once();
        $mockQb->shouldReceive('setParameter')->with('today', m::type(\DateTime::class))->once();

        $mockQb->shouldReceive('orderBy')->with('m.name', 'ASC')->once()->andReturnSelf();

        $this->sut->applyListFilters($mockQb, $mockQ);
    }
}
