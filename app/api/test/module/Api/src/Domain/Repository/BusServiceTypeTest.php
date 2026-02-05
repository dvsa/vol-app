<?php

declare(strict_types=1);

/**
 * BusServiceType repo test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\BusServiceType as Repo;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Doctrine\ORM\QueryBuilder;
use Mockery as m;

/**
 * BusServiceType repo test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class BusServiceTypeTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(Repo::class);
    }

    public function testApplyListFilters(): void
    {
        $this->setUpSut(Repo::class, true);

        $mockQb = m::mock(QueryBuilder::class);
        $mockQ = m::mock(QueryInterface::class);
        $mockQb->shouldReceive('orderBy')->with('m.description', 'ASC')->once()->andReturnSelf();

        $this->sut->applyListFilters($mockQb, $mockQ);
    }
}
