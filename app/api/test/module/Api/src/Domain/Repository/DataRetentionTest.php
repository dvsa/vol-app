<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\Repository\DataRetention;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Transfer\Query\DataRetention\Records as RecordsQry;
use Mockery as m;

/**
 * Class DataRetentionTest
 */
class DataRetentionTest extends RepositoryTestCase
{
    /** @var DataRetention */
    protected $sut;

    public function setUp(): void
    {
        $this->setUpSut(DataRetention::class, true);
    }

    public function testApplyListJoins(): void
    {
        $this->setUpSut(DataRetention::class, true);

        $mockQb = m::mock(\Doctrine\ORM\QueryBuilder::class);

        $mockQb->shouldReceive('with')->once()->with('dataRetentionRule', 'drr')->andReturnSelf();
        $mockQb->shouldReceive('with')->once()->with('assignedTo', 'u')->andReturnSelf();
        $mockQb->shouldReceive('with')->once()->with('u.contactDetails', 'cd')->andReturnSelf();
        $mockQb->shouldReceive('with')->once()->with('cd.person', 'p')->andReturnSelf();
        $this->sut->shouldReceive('getQueryBuilder')->with()->once()->andReturn($mockQb);

        $this->sut->applyListJoins($mockQb);
    }

    public function testApplyListFiltersRecordsQry(): void
    {
        $query = RecordsQry::create(
            [
                'dataRetentionRuleId' => 13, 'sort' => 'id', 'order' => 'DESC',
                'assignedToUser' => 1
            ]
        );

        /** @var QueryBuilder|m::mock $qb */
        $qb = m::mock(QueryBuilder::class);
        $qb->shouldReceive('expr->eq')->with('drr.isEnabled', 1)->once()->andReturn('expr1');
        $qb->shouldReceive('expr->eq')->with('m.assignedTo', ':assignedToUser')->once()->andReturn('expr2');
        $qb->shouldReceive('expr->eq')->with('m.dataRetentionRule', ':dataRetentionRuleId')->once()->andReturn('expr3');
        $qb->shouldReceive('andWhere')->once()->with('expr1')->andReturnSelf();
        $qb->shouldReceive('andWhere')->once()->with('expr2')->andReturnSelf();
        $qb->shouldReceive('andWhere')->once()->with('expr3')->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('dataRetentionRuleId', 13)->once();
        $qb->shouldReceive('setParameter')->with('assignedToUser', 1)->once();

        $this->sut->applyListFilters($qb, $query);
    }

    public function testApplyListFiltersRecordsQryMarkedForDeletionY(): void
    {
        $query = RecordsQry::create(
            ['dataRetentionRuleId' => 13,
                'sort' => 'id',
                'order' => 'DESC',
                'markedForDeletion' => 'Y',
                'assignedToUser' => 1
            ]
        );

        /** @var QueryBuilder|m::mock $qb */
        $qb = m::mock(QueryBuilder::class);
        $qb->shouldReceive('expr->eq')->with('m.actionConfirmation', ':actionConfirmation')->once()->andReturn('expr0');
        $qb->shouldReceive('expr->eq')->with('drr.isEnabled', 1)->once()->andReturn('expr1');
        $qb->shouldReceive('expr->eq')->with('m.dataRetentionRule', ':dataRetentionRuleId')->once()->andReturn('expr2');
        $qb->shouldReceive('expr->eq')->with('m.assignedTo', ':assignedToUser')->once()->andReturn('expr3');
        $qb->shouldReceive('andWhere')->once()->with('expr0')->andReturnSelf();
        $qb->shouldReceive('andWhere')->once()->with('expr1')->andReturnSelf();
        $qb->shouldReceive('andWhere')->once()->with('expr2')->andReturnSelf();
        $qb->shouldReceive('andWhere')->once()->with('expr3')->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('actionConfirmation', 1)->once();
        $qb->shouldReceive('setParameter')->with('dataRetentionRuleId', 13)->once();
        $qb->shouldReceive('setParameter')->with('assignedToUser', 1)->once();

        $this->sut->applyListFilters($qb, $query);
    }

    public function testApplyListFiltersRecordsQryMarkedForDeletionN(): void
    {
        $query = RecordsQry::create(
            ['dataRetentionRuleId' => 13,
                'sort' => 'id',
                'order' => 'DESC',
                'markedForDeletion' => 'N',
                'assignedToUser' => 1
            ]
        );

        /** @var QueryBuilder|m::mock $qb */
        $qb = m::mock(QueryBuilder::class);
        $qb->shouldReceive('expr->eq')->with('m.actionConfirmation', ':actionConfirmation')->once()->andReturn('expr0');
        $qb->shouldReceive('expr->eq')->with('drr.isEnabled', 1)->once()->andReturn('expr1');
        $qb->shouldReceive('expr->eq')->with('m.dataRetentionRule', ':dataRetentionRuleId')->once()->andReturn('expr2');
        $qb->shouldReceive('expr->eq')->with('m.assignedTo', ':assignedToUser')->once()->andReturn('expr3');
        $qb->shouldReceive('andWhere')->once()->with('expr0')->andReturnSelf();
        $qb->shouldReceive('andWhere')->once()->with('expr1')->andReturnSelf();
        $qb->shouldReceive('andWhere')->once()->with('expr2')->andReturnSelf();
        $qb->shouldReceive('andWhere')->once()->with('expr3')->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('actionConfirmation', 0)->once();
        $qb->shouldReceive('setParameter')->with('dataRetentionRuleId', 13)->once();
        $qb->shouldReceive('setParameter')->with('assignedToUser', 1)->once();

        $this->sut->applyListFilters($qb, $query);
    }

    public function testApplyListFiltersRecordsQryWithNextReviewDeferred(): void
    {
        $query = RecordsQry::create(
            ['dataRetentionRuleId' => 13,
                'sort' => 'id',
                'order' => 'DESC',
                'nextReview' => 'deferred',
                'assignedToUser' => 1
            ]
        );

        $today = (new DateTime())->format('Y-m-d');
        $qb = $this->createMockQb('BLAH');
        $this->mockCreateQueryBuilder($qb);

        $this->sut->applyListFilters($qb, $query);

        $expectedQuery = 'BLAH '
            . 'AND m.nextReviewDate > [[' . $today . ']] '
            . 'AND m.assignedTo = [[1]] '
            . 'AND drr.isEnabled = 1 '
            . 'AND m.dataRetentionRule = [[13]]';

        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testApplyListFiltersRecordsQryWithNextReviewPending(): void
    {
        $query = RecordsQry::create(
            ['dataRetentionRuleId' => 13,
                'sort' => 'id',
                'order' => 'DESC',
                'nextReview' => 'pending',
                'assignedToUser' => 1
            ]
        );

        $today = (new DateTime())->format('Y-m-d');
        $qb = $this->createMockQb('BLAH');
        $this->mockCreateQueryBuilder($qb);

        $this->sut->applyListFilters($qb, $query);

        $expectedQuery = 'BLAH '
            . 'AND (m.nextReviewDate IS NULL OR m.nextReviewDate <= [[' . $today . ']]) AND m.assignedTo = [[1]] '
            . 'AND drr.isEnabled = 1 '
            . 'AND m.dataRetentionRule = [[13]]';

        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testApplyListFiltersRecordsQryWithAssignedToUser(): void
    {
        $query = RecordsQry::create(
            ['dataRetentionRuleId' => 13,
                'sort' => 'id',
                'order' => 'DESC',
                'assignedToUser' => 1
            ]
        );

        /** @var QueryBuilder|m::mock $qb */
        $qb = m::mock(QueryBuilder::class);
        $qb->shouldReceive('expr->eq')->with('m.assignedTo', ':assignedToUser')->once()->andReturn('expr0');
        $qb->shouldReceive('expr->eq')->with('drr.isEnabled', 1)->once()->andReturn('expr1');
        $qb->shouldReceive('expr->eq')->with('m.dataRetentionRule', ':dataRetentionRuleId')->once()->andReturn('expr2');
        $qb->shouldReceive('andWhere')->once()->with('expr0')->andReturnSelf();
        $qb->shouldReceive('andWhere')->once()->with('expr1')->andReturnSelf();
        $qb->shouldReceive('andWhere')->once()->with('expr2')->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('assignedToUser', 1)->once();
        $qb->shouldReceive('setParameter')->with('dataRetentionRuleId', 13)->once();

        $this->sut->applyListFilters($qb, $query);
    }

    public function testApplyListFiltersRecordsQryWithAssignedToUserUnassigned(): void
    {
        $query = RecordsQry::create(
            ['dataRetentionRuleId' => 13,
                'sort' => 'id',
                'order' => 'DESC',
                'assignedToUser' => 'unassigned'
            ]
        );

        /** @var QueryBuilder|m::mock $qb */
        $qb = m::mock(QueryBuilder::class);
        $qb->shouldReceive('expr->isNull')->with('m.assignedTo')->once()->andReturn('expr0');
        $qb->shouldReceive('expr->eq')->with('drr.isEnabled', 1)->once()->andReturn('expr1');
        $qb->shouldReceive('expr->eq')->with('m.dataRetentionRule', ':dataRetentionRuleId')->once()->andReturn('expr2');
        $qb->shouldReceive('andWhere')->once()->with('expr0')->andReturnSelf();
        $qb->shouldReceive('andWhere')->once()->with('expr1')->andReturnSelf();
        $qb->shouldReceive('andWhere')->once()->with('expr2')->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('dataRetentionRuleId', 13)->once();

        $this->sut->applyListFilters($qb, $query);
    }

    public function testApplyListFiltersRecordsQryWithAssignedToUserAll(): void
    {
        $query = RecordsQry::create(
            ['dataRetentionRuleId' => 13,
                'sort' => 'id',
                'order' => 'DESC',
                'assignedToUser' => 'all'
            ]
        );

        /** @var QueryBuilder|m::mock $qb */
        $qb = m::mock(QueryBuilder::class);
        $qb->shouldReceive('expr->eq')->with('drr.isEnabled', 1)->once()->andReturn('expr1');
        $qb->shouldReceive('expr->eq')->with('m.dataRetentionRule', ':dataRetentionRuleId')->once()->andReturn('expr2');
        $qb->shouldReceive('andWhere')->once()->with('expr1')->andReturnSelf();
        $qb->shouldReceive('andWhere')->once()->with('expr2')->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('dataRetentionRuleId', 13)->once();

        $this->sut->applyListFilters($qb, $query);
    }

    public function testApplyListFiltersRecordsQryWithGoodsLicence(): void
    {
        $query = RecordsQry::create(
            [
                'dataRetentionRuleId' => 13,
                'goodsOrPsv' => 'lcat_gv',
                'assignedToUser' => 1
            ]
        );

        /** @var QueryBuilder|m::mock $qb */
        $qb = m::mock(QueryBuilder::class);
        $qb->shouldReceive('expr->eq')->with('m.goodsOrPsv', ':goodsOrPsv')->once()->andReturn('expr1');
        $qb->shouldReceive('expr->eq')->with('drr.isEnabled', 1)->once()->andReturn('expr2');
        $qb->shouldReceive('expr->eq')->with('m.dataRetentionRule', ':dataRetentionRuleId')->once()->andReturn('expr3');
        $qb->shouldReceive('expr->eq')->with('m.assignedTo', ':assignedToUser')->once()->andReturn('expr4');
        $qb->shouldReceive('andWhere')->once()->with('expr1')->andReturnSelf();
        $qb->shouldReceive('andWhere')->once()->with('expr2')->andReturnSelf();
        $qb->shouldReceive('andWhere')->once()->with('expr3')->andReturnSelf();
        $qb->shouldReceive('andWhere')->once()->with('expr4')->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('dataRetentionRuleId', 13)->once();
        $qb->shouldReceive('setParameter')->with('goodsOrPsv', 'lcat_gv')->once();
        $qb->shouldReceive('setParameter')->with('assignedToUser', 1)->once();

        $this->sut->applyListFilters($qb, $query);
    }

    public function testRunCleanupProc(): void
    {
        $mockStatement = m::mock();
        $mockStatement
            ->shouldReceive('execute')
            ->once()
            ->with()
            ->andReturn(true)
            ->shouldReceive('closeCursor')
            ->andReturn(true)
            ->shouldReceive('rowCount')
            ->andReturn(1)
            ->shouldReceive('nextRowset');
        $this->em->shouldReceive('getConnection->getNativeConnection->prepare')->with('CALL sp_dr_cleanup(99, 123, 0)')->once()
            ->andReturn($mockStatement);

        $result = $this->sut->runCleanupProc(123, 99);

        $this->assertTrue($result);
    }

    public function testFetchAllProcessedForRule(): void
    {
        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('getResult')
                ->with(Query::HYDRATE_ARRAY)->once()
                ->andReturn('RESULT')
                ->getMock()
        );

        $this->em->shouldReceive('getFilters->isEnabled')->with('soft-deleteable')->once()->andReturn([]);
        $this->em->shouldReceive('getFilters->enable')->with('soft-deleteable')->once()->andReturn([]);

        $this->assertEquals(
            'RESULT',
            $this->sut->fetchAllProcessedForRule(12, new \DateTime('2012-02-20'), new \DateTime('2017-12-10'))
        );

        $expectedQuery = 'BLAH '
            . 'AND m.dataRetentionRule = [[12]] '
            . 'AND m.deletedDate >= [[2012-02-20 00:00:00]] '
            . 'AND m.deletedDate < [[2017-12-11 00:00:00]]';
        $this->assertEquals($expectedQuery, $this->query);
    }
}
