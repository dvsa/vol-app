<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\BusServiceType as Repo;
use Dvsa\Olcs\Api\Entity\Bus\BusServiceType as Entity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Mockery as m;

final class BusServiceTypeTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    public function testApplyListFilters(): void
    {
        $qb = $this->createRealQb();

        $this->sut->applyListFilters($qb, m::mock(QueryInterface::class));

        $this->assertSame(
            'SELECT m FROM ' . Entity::class . ' m ORDER BY m.description ASC',
            $qb->getDQL(),
        );
    }
}
