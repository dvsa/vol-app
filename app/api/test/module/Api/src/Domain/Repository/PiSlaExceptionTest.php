<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\PiSlaException as Repo;
use Dvsa\Olcs\Api\Entity\Pi\PiSlaException as Entity;

final class PiSlaExceptionTest extends RepositoryTestCase
{
    private const string FROM = ' FROM ' . Entity::class . ' m';

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('emptyOrPopulatedProvider')]
    public function testFetchByPi(array $results): void
    {
        $qb = $this->createRealQb()->willReturn($results);

        $this->assertSame($results, $this->sut->fetchByPi(1));

        $this->assertSame(
            'SELECT m, se' . self::FROM . ' INNER JOIN m.slaException se'
            . ' WHERE m.pi = :piId'
            . ' ORDER BY se.slaDescription ASC, m.createdOn DESC',
            $qb->getDQL(),
        );
        $this->assertSame(1, $qb->getParameter('piId')->getValue());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('emptyOrPopulatedProvider')]
    public function testFetchByCase(array $results): void
    {
        $qb = $this->createRealQb()->willReturn($results);

        $this->assertSame($results, $this->sut->fetchByCase(2));

        $this->assertSame(
            'SELECT m, se, p' . self::FROM
            . ' INNER JOIN m.pi p INNER JOIN m.slaException se'
            . ' WHERE p.case = :caseId'
            . ' ORDER BY se.slaDescription ASC, m.createdOn DESC',
            $qb->getDQL(),
        );
        $this->assertSame(2, $qb->getParameter('caseId')->getValue());
    }

    public static function emptyOrPopulatedProvider(): \Iterator
    {
        yield 'with results' => [['RESULT']];
        yield 'empty' => [[]];
    }

    /**
     * Active means the exception's effective window covers the check date; an open-ended
     * exception (no effectiveTo) always counts.
     */
    public function testFetchActiveByPi(): void
    {
        $checkDate = new \DateTime('2016-05-01');

        $qb = $this->createRealQb()->willReturn(['RESULT']);

        $this->assertSame(['RESULT'], $this->sut->fetchActiveByPi(1, $checkDate));

        $this->assertSame(
            'SELECT m, se' . self::FROM . ' INNER JOIN m.slaException se'
            . ' WHERE m.pi = :piId AND se.effectiveFrom <= :checkDate'
            . ' AND (se.effectiveTo IS NULL OR se.effectiveTo >= :checkDate)'
            . ' ORDER BY se.slaDescription ASC',
            $qb->getDQL(),
        );
        $this->assertSame($checkDate, $qb->getParameter('checkDate')->getValue());
    }

    public function testFetchActiveByPiDefaultsToNow(): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULT']);

        $this->sut->fetchActiveByPi(1);

        $this->assertInstanceOf(\DateTimeInterface::class, $qb->getParameter('checkDate')->getValue());
    }
}
