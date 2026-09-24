<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\LicenceStatusRule as Repo;
use Dvsa\Olcs\Api\Entity\Licence\LicenceStatusRule as Entity;

final class LicenceStatusRuleTest extends RepositoryTestCase
{
    private const string FROM = ' FROM ' . Entity::class . ' lsr';

    /**
     * withRefdata() joins licenceStatus as w0 and with('licenceStatus') joins it again as w1 —
     * the same association twice, for no effect beyond the extra join.
     */
    private const string JOINS = ' LEFT JOIN lsr.licenceStatus w0 LEFT JOIN lsr.licenceStatus w1'
        . ' LEFT JOIN lsr.licence l LEFT JOIN l.status w2';

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    /**
     * A rule is applied once and then stamped as processed, so each sweep excludes what it has
     * already done. Deleted rules never apply.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('sweepProvider')]
    public function testSweeps(string $method, string $expectedWhere, string $expectedParameter): void
    {
        $date = new \DateTime('2020-01-01');

        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->withNoArgs()->andReturn('RESULT');

        $this->assertSame('RESULT', $this->sut->{$method}($date));

        $this->assertSame(
            'SELECT lsr, w0, w1, l, w2' . self::FROM . self::JOINS . ' WHERE ' . $expectedWhere,
            $qb->getDQL(),
        );
        $this->assertSame($date, $qb->getParameter($expectedParameter)->getValue());
    }

    public static function sweepProvider(): \Iterator
    {
        yield 'revoke, curtail or suspend' => [
            'fetchRevokeCurtailSuspend',
            'lsr.startProcessedDate IS NULL AND lsr.deletedDate IS NULL'
            . ' AND lsr.startDate <= :startDate',
            'startDate',
        ];
        // Returning to valid additionally requires an end date: an open-ended rule never expires.
        yield 'back to valid' => [
            'fetchToValid',
            'lsr.endProcessedDate IS NULL AND lsr.endDate IS NOT NULL'
            . ' AND lsr.deletedDate IS NULL AND lsr.endDate <= :endDate',
            'endDate',
        ];
    }

    public function testApplyFetchJoins(): void
    {
        $qb = $this->createRealQb();

        $this->sut->applyFetchJoins($qb);

        $this->assertSame(
            'SELECT lsr, l, d' . self::FROM
            . ' LEFT JOIN lsr.licence l LEFT JOIN l.decisions d',
            $qb->getDQL(),
        );
    }
}
