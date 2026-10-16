<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\LegacyOffence as Repo;
use Dvsa\Olcs\Api\Entity\Legacy\LegacyOffence as Entity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Mockery as m;

final class LegacyOffenceTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    /** The case is matched as well as the id, so an offence cannot be read from another case. */
    public function testFetchCaseLegacyOffenceUsingId(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_OBJECT)->andReturn(['result']);

        $query = m::mock(QueryInterface::class);
        $query->shouldReceive('getId')->andReturn(99);
        $query->shouldReceive('getCase')->andReturn(24);

        $this->assertSame(
            'result',
            $this->sut->fetchCaseLegacyOffenceUsingId($query, Query::HYDRATE_OBJECT),
        );

        $this->assertSame(
            'SELECT m, w0, w1, w2 FROM ' . Entity::class . ' m'
            . ' LEFT JOIN m.case w0 LEFT JOIN m.createdBy w1 LEFT JOIN m.lastModifiedBy w2'
            . ' WHERE m.id = :byId AND m.case = :byCase',
            $qb->getDQL(),
        );
        $this->assertSame(99, $qb->getParameter('byId')->getValue());
        $this->assertSame(24, $qb->getParameter('byCase')->getValue());
    }
}
