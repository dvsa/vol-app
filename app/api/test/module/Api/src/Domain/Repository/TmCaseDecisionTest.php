<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\TmCaseDecision as Repo;
use Dvsa\Olcs\Api\Entity\Tm\TmCaseDecision as Entity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Mockery as m;

final class TmCaseDecisionTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    /**
     * A case can be decided more than once; the newest decision is the one that stands, so the
     * ordering is what makes this "latest".
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('latestDecisionProvider')]
    public function testFetchLatestUsingCase(array $results, mixed $expected): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_OBJECT)->andReturn($results);

        $query = m::mock(QueryInterface::class);
        $query->shouldReceive('getCase')->andReturn(24);

        $this->assertSame($expected, $this->sut->fetchLatestUsingCase($query, Query::HYDRATE_OBJECT));

        $this->assertSame(
            'SELECT m, w0, w1, w2 FROM ' . Entity::class . ' m'
            . ' LEFT JOIN m.decision w0 LEFT JOIN m.rehabMeasures w1'
            . ' LEFT JOIN m.unfitnessReasons w2'
            . ' WHERE m.case = :byCase'
            . ' ORDER BY m.id DESC',
            $qb->getDQL(),
        );
        $this->assertSame(24, $qb->getParameter('byCase')->getValue());
    }

    public static function latestDecisionProvider(): \Iterator
    {
        yield 'a decision exists' => [['result', 'older'], 'result'];
        yield 'no decision' => [[], false];
    }
}
