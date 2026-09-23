<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\Venue as Repo;
use Dvsa\Olcs\Api\Entity\Venue as Entity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Mockery as m;

final class VenueTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    /**
     * A venue with no end date is open indefinitely; one with an end date drops out of the list on
     * the day it closes.
     */
    public function testApplyListFilters(): void
    {
        $qb = $this->createRealQb();

        $query = m::mock(QueryInterface::class);
        $query->shouldReceive('getTrafficArea')->andReturn('B');

        $this->sut->applyListFilters($qb, $query);

        $this->assertSame(
            'SELECT m FROM ' . Entity::class . ' m'
            . ' WHERE m.trafficArea = :trafficArea'
            . ' AND (m.endDate IS NULL OR m.endDate > :today)'
            . ' ORDER BY m.name ASC',
            $qb->getDQL(),
        );
        $this->assertSame('B', $qb->getParameter('trafficArea')->getValue());
        $this->assertInstanceOf(\DateTime::class, $qb->getParameter('today')->getValue());
    }
}
