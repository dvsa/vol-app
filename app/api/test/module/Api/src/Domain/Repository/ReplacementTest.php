<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\Replacement as Repo;
use Dvsa\Olcs\Api\Entity\System\Replacement as Entity;

final class ReplacementTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    public function testFetchAll(): void
    {
        $qb = $this->newRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_ARRAY)->andReturn(['RESULTS']);
        $this->em->expects('createQueryBuilder')->withNoArgs()->andReturn($qb);

        $this->assertSame(['RESULTS'], $this->sut->fetchAll(Query::HYDRATE_ARRAY));

        $this->assertSame('SELECT r FROM ' . Entity::class . ' r', $qb->getDQL());
    }
}
