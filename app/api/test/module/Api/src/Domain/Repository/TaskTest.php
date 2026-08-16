<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\Repository\Task::class)]
final class TaskTest extends RepositoryTestCase
{
    /** @var m\MockInterface | Repository\Task */
    protected $sut;

    #[\Override]
    public function setUp(): void
    {
        $this->setUpSut(Repository\Task::class);
    }

    public function testFetchByOrganisation(): void
    {
        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock(\Doctrine\ORM\Query::class)->shouldReceive('execute')
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
            m::mock(\Doctrine\ORM\Query::class)->shouldReceive('execute')
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
            m::mock(\Doctrine\ORM\Query::class)->shouldReceive('execute')
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
            m::mock(\Doctrine\ORM\Query::class)->shouldReceive('execute')
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
            m::mock(\Doctrine\ORM\Query::class)->shouldReceive('execute')
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
            m::mock(\Doctrine\ORM\Query::class)->shouldReceive('execute')
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

        $this->assertNotInstanceOf(\Dvsa\Olcs\Api\Entity\User\Team::class, $this->sut->getTeamReference(null, $userId));
        $this->assertNotInstanceOf(\Dvsa\Olcs\Api\Entity\User\Team::class, $this->sut->getTeamReference(null, null));
    }

    public function testGetTeamReferenceByTeam(): void
    {
        $teamId = 999;

        $team = m::mock(Entity\User\Team::class);
        $this->em->shouldReceive('getReference')->once()->with(Entity\User\Team::class, $teamId)->andReturn($team);

        $this->assertSame($team, $this->sut->getTeamReference($teamId, null));
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

        $this->assertEquals('EXPECT', $this->sut->getTeamReference(null, $userId));
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

        $expr1 = $this->mockExprEq('m.application', ':application');
        $mockQb->shouldReceive('expr->eq')->with('m.application', ':application')->once()->andReturn($expr1);
        $mockQb->shouldReceive('andWhere')->with($expr1)->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('application', 1)->once();

        $expr2 = $this->mockExprEq('m.description', ':description');
        $mockQb->shouldReceive('expr->eq')->with('m.description', ':description')->once()->andReturn($expr2);
        $mockQb->shouldReceive('andWhere')->with($expr2)->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('description', 'foo')->once();

        $expr3 = $this->mockExprEq('m.isClosed', ':isClosed');
        $mockQb->shouldReceive('expr->eq')->with('m.isClosed', ':isClosed')->once()->andReturn($expr3);
        $mockQb->shouldReceive('andWhere')->with($expr3)->once()->andReturnSelf();
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

        $expr1 = $this->mockExprIn('m.licence', ':licenceIds');
        $qb->shouldReceive('expr->in')->with('m.licence', ':licenceIds')->once()->andReturn($expr1);
        $qb->shouldReceive('andWhere')->with($expr1)->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('licenceIds', $licenceIds)->once()->andReturnSelf();

        $expr2 = $this->mockExprEq('m.description', ':description');
        $qb->shouldReceive('expr->eq')->with('m.description', ':description')->once()->andReturn($expr2);
        $qb->shouldReceive('andWhere')->with($expr2)->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('description', $description)->once()->andReturnSelf();

        $expr3 = $this->mockExprEq('m.isClosed', ':isClosed');
        $qb->shouldReceive('expr->eq')->with('m.isClosed', ':isClosed')->once()->andReturn($expr3);
        $qb->shouldReceive('andWhere')->with($expr3)->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('isClosed', 0)->once()->andReturnSelf();

        $expr4 = $this->mockExprEq('m.category', ':categoryId');
        $qb->shouldReceive('expr->eq')->with('m.category', ':categoryId')->once()->andReturn($expr4);
        $qb->shouldReceive('andWhere')->with($expr4)->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('categoryId', $categoryId)->once()->andReturnSelf();

        $expr5 = $this->mockExprEq('m.subCategory', ':subCategoryId');
        $qb->shouldReceive('expr->eq')->with('m.subCategory', ':subCategoryId')->once()->andReturn($expr5);
        $qb->shouldReceive('andWhere')->with($expr5)->once()->andReturnSelf();
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

        $expr1 = $this->mockExprEq('m.surrender', ':surrenderId');
        $qb->shouldReceive('expr->eq')
            ->with('m.surrender', ':surrenderId')
            ->once()
            ->andReturn($expr1);

        $qb->shouldReceive('where')
            ->with($expr1)
            ->andReturnSelf();

        $qb->shouldReceive('setParameter')
            ->with('surrenderId', $surrenderId)
            ->once()
            ->andReturnSelf();

        $expr2 = $this->mockExprEq('m.isClosed', ':isClosed');
        $qb->shouldReceive('expr->eq')
            ->with('m.isClosed', ':isClosed')
            ->once()
            ->andReturn($expr2);

        $qb->shouldReceive('andWhere')
            ->with($expr2)
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
