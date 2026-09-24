<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\BusShortNotice as Repo;
use Dvsa\Olcs\Api\Entity\Bus\BusShortNotice as Entity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Mockery as m;

final class BusShortNoticeTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    public function testFetchByBusReg(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('execute')->withNoArgs()->andReturn(['result']);

        $query = m::mock(QueryInterface::class);
        $query->shouldReceive('getId')->andReturn(15);

        $this->assertSame(['result'], $this->sut->fetchByBusReg($query));

        $this->assertSame(
            'SELECT m, b FROM ' . Entity::class . ' m'
            . ' LEFT JOIN m.busReg b'
            . ' WHERE b.id = :busReg',
            $qb->getDQL(),
        );
        $this->assertSame(15, $qb->getParameter('busReg')->getValue());
    }
}
