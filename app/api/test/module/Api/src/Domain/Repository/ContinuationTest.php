<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\Continuation as Repo;
use Dvsa\Olcs\Api\Entity\Licence\Continuation as Entity;

final class ContinuationTest extends RepositoryTestCase
{
    private const string FROM = ' FROM ' . Entity::class . ' m LEFT JOIN m.trafficArea ta';

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class);
    }

    public function testFetchWithTa(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getSingleResult')->andReturn(['result']);

        $this->assertSame(['result'], $this->sut->fetchWithTa(1));

        $this->assertSame('SELECT m, ta' . self::FROM . ' WHERE m.id = :byId', $qb->getDQL());
        $this->assertSame(1, $qb->getParameter('byId')->getValue());
    }

    public function testFetchContinuation(): void
    {
        $qb = $this->createRealQb()->willReturn(['result']);

        $this->assertSame(['result'], $this->sut->fetchContinuation(1, 2015, 'B'));

        $this->assertSame(
            'SELECT m, ta' . self::FROM
            . ' WHERE m.month = :month AND m.year = :year AND ta.id = :trafficArea',
            $qb->getDQL(),
        );
        $this->assertSame(1, $qb->getParameter('month')->getValue());
        $this->assertSame(2015, $qb->getParameter('year')->getValue());
        $this->assertSame('B', $qb->getParameter('trafficArea')->getValue());
    }
}
