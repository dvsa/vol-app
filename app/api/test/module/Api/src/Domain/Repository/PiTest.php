<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\Pi as PiRepo;
use Dvsa\Olcs\Api\Entity\Pi\Pi as Entity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Mockery as m;

final class PiTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(PiRepo::class);
    }

    public function testFetchUsingCase(): void
    {
        $pi = m::mock(Entity::class);

        $command = m::mock(QueryInterface::class);
        $command->shouldReceive('getId')->andReturn(24);

        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_OBJECT)->andReturn([$pi]);

        $this->assertSame($pi, $this->sut->fetchUsingCase($command, Query::HYDRATE_OBJECT));

        // withRefdata() joins the RefData associations as w0..w5 before the explicit with()
        // calls. m.tmDecisions appears twice (w4 and w11) — see the migration findings.
        $this->assertSame(
            'SELECT m, w0, w1, w2, w3, w4, w5, w6, w7, w8, w9, w10, w11, w12, c, w13, s, u, cd, p'
            . ' FROM ' . Entity::class . ' m'
            . ' LEFT JOIN m.agreedByTcRole w0 LEFT JOIN m.decidedByTcRole w1 LEFT JOIN m.piStatus w2'
            . ' LEFT JOIN m.writtenOutcome w3 LEFT JOIN m.tmDecisions w4 LEFT JOIN m.piTypes w5'
            . ' LEFT JOIN m.agreedByTc w6 LEFT JOIN m.assignedTo w7 LEFT JOIN m.decidedByTc w8'
            . ' LEFT JOIN m.reasons w9 LEFT JOIN m.decisions w10 LEFT JOIN m.tmDecisions w11'
            . ' LEFT JOIN m.piHearings w12 LEFT JOIN m.case c LEFT JOIN c.transportManager w13'
            . ' LEFT JOIN m.piSlaExceptions s LEFT JOIN m.createdBy u'
            . ' LEFT JOIN u.contactDetails cd LEFT JOIN cd.person p'
            . ' WHERE m.case = :byId',
            $qb->getDQL(),
        );
        $this->assertSame(24, $qb->getParameter('byId')->getValue());
    }
}
