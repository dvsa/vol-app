<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository\Answer;
use Dvsa\Olcs\Api\Entity\Generic\Answer as AnswerEntity;
use Mockery as m;

/**
 * Answer test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class AnswerTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(Answer::class);
    }

    public function testFetchByQuestionIdAndEntityTypeAndId(): void
    {
        $questionId = 47;
        $entityType = 'entityTypeName';
        $entityId = 28;
        $answerEntity = m::mock(AnswerEntity::class);

        $queryBuilder = m::mock(QueryBuilder::class);
        $this->em->shouldReceive('createQueryBuilder')->once()->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('select')
            ->with('a')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('from')
            ->with(AnswerEntity::class, 'a')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('innerJoin')
            ->with('a.questionText', 'qt')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('where')
            ->with('IDENTITY(qt.question) = ?1')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->with('IDENTITY(a.entityTypeName) = ?2')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with(1, $questionId)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with(2, $entityId)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('getQuery->getSingleResult')
            ->once()
            ->andReturn($answerEntity);

        $this->assertSame(
            $answerEntity,
            $this->sut->fetchByQuestionIdAndEntityTypeAndId($questionId, $entityType, $entityId)
        );
    }

    public function testFetchByQuestionIdAndEntityTypeAndIdNotFound(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Answer not found');

        $questionId = 47;
        $entityType = 'entityTypeName';
        $entityId = 28;

        $queryBuilder = m::mock(QueryBuilder::class);
        $this->em->shouldReceive('createQueryBuilder')->once()->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('select')
            ->with('a')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('from')
            ->with(AnswerEntity::class, 'a')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('innerJoin')
            ->with('a.questionText', 'qt')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('where')
            ->with('IDENTITY(qt.question) = ?1')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->with('IDENTITY(a.entityTypeName) = ?2')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with(1, $questionId)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with(2, $entityId)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('getQuery->getSingleResult')
            ->once()
            ->andThrow(new NoResultException());

        $this->sut->fetchByQuestionIdAndEntityTypeAndId($questionId, $entityType, $entityId);
    }
}
