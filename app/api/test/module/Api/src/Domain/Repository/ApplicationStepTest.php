<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\NoResultException;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository\ApplicationStep as Repo;
use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep as Entity;
use Dvsa\OlcsTest\Support\TestQueryBuilder;
use Mockery as m;

final class ApplicationStepTest extends RepositoryTestCase
{
    private const string DQL = 'SELECT ast FROM ' . Entity::class . ' ast'
        . ' INNER JOIN ast.question q'
        . ' WHERE IDENTITY(ast.applicationPath) = ?1 AND q.slug = ?2';

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    public function testFetchByApplicationPathIdAndSlug(): void
    {
        $applicationStep = m::mock(Entity::class);

        $qb = $this->expectEntityManagerQb();
        $qb->stubbedQuery()->expects('getSingleResult')->withNoArgs()->andReturn($applicationStep);

        $this->assertSame(
            $applicationStep,
            $this->sut->fetchByApplicationPathIdAndSlug(22, 'removals-eligibility'),
        );

        $this->assertSame(self::DQL, $qb->getDQL());
        $this->assertSame(22, $qb->getParameter(1)->getValue());
        $this->assertSame('removals-eligibility', $qb->getParameter(2)->getValue());
    }

    public function testFetchByApplicationPathIdAndSlugNotFound(): void
    {
        $qb = $this->expectEntityManagerQb();
        $qb->stubbedQuery()->expects('getSingleResult')->withNoArgs()->andThrow(new NoResultException());

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage(
            'Unable to find application step with path id 22 and slug removals-eligibility',
        );

        $this->sut->fetchByApplicationPathIdAndSlug(22, 'removals-eligibility');
    }

    private function expectEntityManagerQb(): TestQueryBuilder
    {
        $qb = $this->newRealQb();

        $this->em->expects('createQueryBuilder')->withNoArgs()->andReturn($qb);

        return $qb;
    }
}
