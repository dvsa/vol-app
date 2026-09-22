<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\NoResultException;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository\Answer as Repo;
use Dvsa\Olcs\Api\Entity\Generic\Answer as Entity;
use Mockery as m;

final class AnswerTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    /**
     * The entity type names the association to match on, and is interpolated into the DQL rather
     * than bound — so it has to be a real association. Both callers pass one.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('entityTypeProvider')]
    public function testFetchByQuestionIdAndEntityTypeAndId(string $entityType): void
    {
        $answer = m::mock(Entity::class);

        $qb = $this->expectEntityManagerQb();
        $qb->stubbedQuery()->expects('getSingleResult')->withNoArgs()->andReturn($answer);

        $this->assertSame($answer, $this->sut->fetchByQuestionIdAndEntityTypeAndId(47, $entityType, 28));

        $this->assertSame(
            'SELECT a FROM ' . Entity::class . ' a'
            . ' INNER JOIN a.questionText qt'
            . ' WHERE IDENTITY(qt.question) = ?1 AND IDENTITY(a.' . $entityType . ') = ?2',
            $qb->getDQL(),
        );
        $this->assertSame(47, $qb->getParameter(1)->getValue());
        $this->assertSame(28, $qb->getParameter(2)->getValue());

        $this->compileDql($qb->getDQL());
    }

    public static function entityTypeProvider(): \Iterator
    {
        yield 'an IRHP application' => ['irhpApplication'];
        yield 'an IRHP permit application' => ['irhpPermitApplication'];
    }

    public function testFetchByQuestionIdAndEntityTypeAndIdNotFound(): void
    {
        $qb = $this->expectEntityManagerQb();
        $qb->stubbedQuery()->expects('getSingleResult')->withNoArgs()->andThrow(new NoResultException());

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Answer not found');

        $this->sut->fetchByQuestionIdAndEntityTypeAndId(47, 'irhpApplication', 28);
    }

    private function expectEntityManagerQb(): \Dvsa\OlcsTest\Support\TestQueryBuilder
    {
        $qb = $this->newRealQb();

        $this->em->expects('createQueryBuilder')->withNoArgs()->andReturn($qb);

        return $qb;
    }
}
