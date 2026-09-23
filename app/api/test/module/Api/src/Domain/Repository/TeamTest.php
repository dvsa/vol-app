<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Query\Team\TeamListByTrafficArea;
use Dvsa\Olcs\Api\Domain\Repository\Team as TeamRepo;
use Dvsa\Olcs\Api\Entity\User\Team as Entity;

final class TeamTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(TeamRepo::class, true);
    }

    public function testApplyTrafficAreaListFilterApplied(): void
    {
        $trafficAreas = ['A', 'B'];

        $qb = $this->createRealQb();

        $this->sut->applyListFilters($qb, TeamListByTrafficArea::create(['trafficAreas' => $trafficAreas]));

        $this->assertSame(
            'SELECT m FROM ' . Entity::class . ' m WHERE m.trafficArea IN(:byTrafficAreas)',
            $qb->getDQL(),
        );
        $this->assertSame($trafficAreas, $qb->getParameter('byTrafficAreas')->getValue());
    }

    public function testFetchByName(): void
    {
        $qb = $this->createRealQb()->willReturn(['result']);

        $this->assertSame(['result'], $this->sut->fetchByName('foo'));

        $this->assertSame(
            'SELECT m FROM ' . Entity::class . ' m WHERE m.name = :name',
            $qb->getDQL(),
        );
        $this->assertSame('foo', $qb->getParameter('name')->getValue());
    }

    public function testFetchWithPrinters(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getSingleResult')->with(1)->andReturn('result');

        $this->assertSame('result', $this->sut->fetchWithPrinters(1, 1));

        // Team has no RefData associations, so withRefdata() contributes no joins here.
        $this->assertSame(
            'SELECT m, tp, tpp, pu, ps FROM ' . Entity::class . ' m'
            . ' LEFT JOIN m.teamPrinters tp LEFT JOIN tp.printer tpp'
            . ' LEFT JOIN tp.user pu LEFT JOIN tp.subCategory ps'
            . ' WHERE m.id = :byId',
            $qb->getDQL(),
        );
        $this->assertSame(1, $qb->getParameter('byId')->getValue());
    }
}
