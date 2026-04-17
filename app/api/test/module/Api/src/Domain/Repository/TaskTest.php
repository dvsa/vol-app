<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\Repository\Task::class)]
class TaskTest extends RepositoryTestCase
{
    /** @var m\MockInterface | Repository\Task */
    protected $sut;

    public function setUp(): void
    {
        $this->setUpSut(Repository\Task::class);
    }

    public function testFetchByOrganisation(): void
    {
        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn(['RESULTS'])
                ->getMock()
        );
        $this->assertEquals(['RESULTS'], $this->sut->fetchByIrfoOrganisation('ORG1'));

        $expectedQuery = 'BLAH AND m.irfoOrganisation = [[ORG1]]';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchByTransportManager(): void
    {
        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn(['RESULTS'])
                ->getMock()
        );
        $this->assertEquals(['RESULTS'], $this->sut->fetchByTransportManager('TM1'));

        $expectedQuery = 'BLAH AND m.transportManager = [[TM1]]';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchByUser(): void
    {
        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn(['RESULTS'])
                ->getMock()
        );
        $this->assertEquals(['RESULTS'], $this->sut->fetchByUser('U1'));

        $expectedQuery = 'BLAH AND m.assignedToUser = [[U1]]';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchByUserWithOpenOnly(): void
    {
        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn(['RESULTS'])
                ->getMock()
        );
        $this->assertEquals(['RESULTS'], $this->sut->fetchByUser('U1', true));

        $expectedQuery = 'BLAH AND m.assignedToUser = [[U1]] AND m.isClosed = [[N]]';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchForTmCaseDecision(): void
    {
        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getSingleResult')
                ->andReturn(['RESULTS'])
                ->getMock()
        );
        $case = 3;
        $transportManager = 4;

        $this->assertEquals(['RESULTS'], $this->sut->fetchForTmCaseDecision($case, $transportManager, 'subcat'));

        $expectedQuery =
            'BLAH AND m.transportManager = [[4]] AND m.case = [[3]] ' .
            'AND m.category = [[5]] AND m.subCategory = [[subcat]]';

        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchForAssignedToSubmission(): void
    {
        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getOneOrNullResult')
                ->andReturn(['RESULTS'])
                ->getMock()
        );
        $submission = 3;

        $this->assertEquals(['RESULTS'], $this->sut->fetchAssignedToSubmission($submission));

        $expectedQuery =
            'BLAH AND m.submission = [[3]] ' .
            'AND m.category = [[10]] AND m.subCategory = [[114]] AND m.isClosed = 0';

        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFlagUrgentsTasks(): void
    {
        $queryResponse = m::mock();
        $queryResponse->shouldReceive('fetchOne')->once()->andReturn(65);

        $query = m::mock();
        $query->shouldReceive('execute')->once()->with()->andReturn($queryResponse);

        $this->dbQueryService->shouldReceive('get')
            ->with('Task\FlagUrgentTasks')
            ->andReturn($query);

        $result = $this->sut->flagUrgentsTasks();

        $this->assertSame(65, $result);
    }

    public function testGetTeamReferenceNull(): void
    {
        $userId = 6555;

        $this->em->shouldReceive('getReference')->once()->with(Entity\User\User::class, $userId)->andReturn(null);

        static::assertNull($this->sut->getTeamReference(null, $userId));
        static::assertNull($this->sut->getTeamReference(null, null));
    }

    public function testGetTeamReferenceByTeam(): void
    {
        $teamId = 999;

        $this->em->shouldReceive('getReference')->once()->with(Entity\User\Team::class, $teamId)->andReturn('EXPECT');

        static::assertEquals('EXPECT', $this->sut->getTeamReference($teamId, null));
    }

    public function testGetTeamReferenceByUser(): void
    {
        $userId = 666;

        $mockUser = m::mock(Entity\User\User::class);
        $mockUser->shouldReceive('getTeam')->once()->andReturn('EXPECT');

        $this->em
            ->shouldReceive('getReference')
            ->once()
            ->with(Entity\User\User::class, $userId)
            ->andReturn($mockUser);

        static::assertEquals('EXPECT', $this->sut->getTeamReference(null, $userId));
    }

    public function testFetchByAppIdAndDescription(): void
    {
        $this->setUpSut(Repository\Task::class);

        /** @var QueryBuilder $qb */
        $mockQb = m::mock(QueryBuilder::class);

        $this->em
            ->shouldReceive('getRepository->createQueryBuilder')
            ->once()
            ->andReturn($mockQb);

        $mockQb->shouldReceive('expr->eq')->with('m.application', ':application')->once()->andReturn('EXPR1');
        $mockQb->shouldReceive('andWhere')->with('EXPR1')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('application', 1)->once();

        $mockQb->shouldReceive('expr->eq')->with('m.description', ':description')->once()->andReturn('EXPR2');
        $mockQb->shouldReceive('andWhere')->with('EXPR2')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('description', 'foo')->once();

        $mockQb->shouldReceive('expr->eq')->with('m.isClosed', ':isClosed')->once()->andReturn('EXPR3');
        $mockQb->shouldReceive('andWhere')->with('EXPR3')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('isClosed', 'N')->once();

        $mockQb->shouldReceive('getQuery->getResult')->andReturn(['result']);

        $this->assertEquals(['result'], $this->sut->fetchByAppIdAndDescription(1, 'foo'));
    }

    public function testFetchOpenedTasksForLicences(): void
    {
        $licenceIds = [];
        $categoryId = 1;
        $subCategoryId = 2;
        $description = 'foo';

        $this->setUpSut(Repository\Task::class);

        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->once()
            ->with($qb)
            ->andReturnSelf()
            ->shouldReceive('withRefdata')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('with')
            ->with('licence', 'l')
            ->andReturnSelf()
            ->once()
            ->getMock();

        $qb->shouldReceive('expr->in')->with('m.licence', ':licenceIds')->once()->andReturn('EXPR1');
        $qb->shouldReceive('andWhere')->with('EXPR1')->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('licenceIds', $licenceIds)->once()->andReturnSelf();

        $qb->shouldReceive('expr->eq')->with('m.description', ':description')->once()->andReturn('EXPR2');
        $qb->shouldReceive('andWhere')->with('EXPR2')->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('description', $description)->once()->andReturnSelf();

        $qb->shouldReceive('expr->eq')->with('m.isClosed', ':isClosed')->once()->andReturn('EXPR3');
        $qb->shouldReceive('andWhere')->with('EXPR3')->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('isClosed', 0)->once()->andReturnSelf();

        $qb->shouldReceive('expr->eq')->with('m.category', ':categoryId')->once()->andReturn('EXPR4');
        $qb->shouldReceive('andWhere')->with('EXPR4')->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('categoryId', $categoryId)->once()->andReturnSelf();

        $qb->shouldReceive('expr->eq')->with('m.subCategory', ':subCategoryId')->once()->andReturn('EXPR5');
        $qb->shouldReceive('andWhere')->with('EXPR5')->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('subCategoryId', $subCategoryId)->once()->andReturnSelf();

        $this->em
            ->shouldReceive('getRepository->createQueryBuilder')
            ->once()
            ->andReturn($qb);

        $qb->shouldReceive('getQuery->getResult')->with(Query::HYDRATE_ARRAY)->andReturn(['result']);

        $this->assertEquals(
            ['result'],
            $this->sut->fetchOpenedTasksForLicences($licenceIds, $categoryId, $subCategoryId, $description)
        );
    }

    public function testFetchOpenTasksForSurrender(): void
    {
        $surrenderId = 1;
        $qb = m::mock(QueryBuilder::class);
        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('expr->eq')
            ->with('m.surrender', ':surrenderId')
            ->once()
            ->andReturn('EXPR1');

        $qb->shouldReceive('where')
            ->with('EXPR1')
            ->andReturnSelf();

        $qb->shouldReceive('setParameter')
            ->with('surrenderId', $surrenderId)
            ->once()
            ->andReturnSelf();

        $qb->shouldReceive('expr->eq')
            ->with('m.isClosed', ':isClosed')
            ->once()
            ->andReturn('EXPR2');

        $qb->shouldReceive('andWhere')
            ->with('EXPR2')
            ->andReturnSelf();

        $qb->shouldReceive('setParameter')
            ->with('isClosed', 0)
            ->once()
            ->andReturnSelf();

        $qb->shouldReceive('getQuery->getResult')
            ->andReturn(['result']);

        $this->assertSame(
            ['result'],
            $this->sut->fetchOpenTasksForSurrender($surrenderId)
        );
    }
}
