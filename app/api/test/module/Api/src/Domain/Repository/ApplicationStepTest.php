<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository\ApplicationStep;
use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep as ApplicationStepEntity;
use Mockery as m;

/**
 * Application step test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class ApplicationStepTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(ApplicationStep::class);
    }

    public function testFetchByApplicationPathIdAndSlug(): void
    {
        $applicationPathId = 22;
        $slug = 'removals-eligibility';
        $applicationStepEntity = m::mock(ApplicationStepEntity::class);

        $queryBuilder = m::mock(QueryBuilder::class);
        $this->em->shouldReceive('createQueryBuilder')->once()->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('select')
            ->with('ast')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('from')
            ->with(ApplicationStepEntity::class, 'ast')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('innerJoin')
            ->with('ast.question', 'q')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('where')
            ->with('IDENTITY(ast.applicationPath) = ?1')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->with('q.slug = ?2')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with(1, $applicationPathId)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with(2, $slug)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('getQuery->getSingleResult')
            ->once()
            ->andReturn($applicationStepEntity);

        $this->assertSame(
            $applicationStepEntity,
            $this->sut->fetchByApplicationPathIdAndSlug($applicationPathId, $slug)
        );
    }

    public function testFetchByApplicationPathIdAndSlugNotFound(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage(
            'Unable to find application step with path id 22 and slug removals-eligibility'
        );

        $applicationPathId = 22;
        $slug = 'removals-eligibility';

        $queryBuilder = m::mock(QueryBuilder::class);
        $this->em->shouldReceive('createQueryBuilder')->once()->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('select')
            ->with('ast')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('from')
            ->with(ApplicationStepEntity::class, 'ast')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('innerJoin')
            ->with('ast.question', 'q')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('where')
            ->with('IDENTITY(ast.applicationPath) = ?1')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->with('q.slug = ?2')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with(1, $applicationPathId)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with(2, $slug)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('getQuery->getSingleResult')
            ->once()
            ->andThrow(new NoResultException());

        $this->sut->fetchByApplicationPathIdAndSlug($applicationPathId, $slug);
    }
}
