<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\Note as Repo;
use Dvsa\Olcs\Api\Entity\Note\Note as NoteEntity;
use Doctrine\ORM\Query;

/**
 * NoteTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class NoteTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(Repo::class);
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
        $this->assertEquals(['RESULTS'], $this->sut->fetchByOrganisation('ORG1'));

        $expectedQuery = 'BLAH AND n.organisation = [[ORG1]]';
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

        $expectedQuery = 'BLAH AND n.transportManager = [[TM1]]';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchForOverview(): void
    {
        $qb = m::mock(QueryBuilder::class);
        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('expr->eq')->with('n.application', ':applicationId')->once()->andReturn('cond1');
        $qb->shouldReceive('expr->eq')->with('n.licence', ':licenceId')->once()->andReturn('cond2');
        $qb->shouldReceive('expr->eq')->with('n.noteType', ':noteTypeId')->once()->andReturn('cond3');
        $qb->shouldReceive('expr->eq')->with('n.transportManager', ':tmId')->once()->andReturn('cond4');

        $qb->shouldReceive('andWhere')->with('cond1')->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('applicationId', 1)->once()->andReturnSelf();

        $qb->shouldReceive('andWhere')->with('cond2')->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('licenceId', 7)->once()->andReturnSelf();

        $qb->shouldReceive('andWhere')->with('cond3')->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('noteTypeId', NoteEntity::NOTE_TYPE_CASE)->once()->andReturnSelf();

        $qb->shouldReceive('andWhere')->with('cond4')->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('tmId', 2)->once()->andReturnSelf();

        $qb->shouldReceive('orderBy')->with('n.priority', 'DESC')->once()->andReturnSelf();
        $qb->shouldReceive('addOrderBy')->with('n.createdOn', 'DESC')->once()->andReturnSelf();
        $qb->shouldReceive('setMaxResults')->with(1)->once()->andReturnSelf();

        $qb->shouldReceive('getQuery->getResult')->with(Query::HYDRATE_ARRAY)->once()->andReturn(['RESULT']);
        $this->assertEquals('RESULT', $this->sut->fetchForOverview(7, 1, 2, NoteEntity::NOTE_TYPE_CASE));
    }
}
