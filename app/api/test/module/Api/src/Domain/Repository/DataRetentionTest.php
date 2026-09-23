<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\Query\FilterCollection;
use Dvsa\Olcs\Api\Domain\Repository\DataRetention as Repo;
use Dvsa\Olcs\Api\Entity\DataRetention\DataRetention as Entity;
use Dvsa\Olcs\Transfer\Query\DataRetention\Records as RecordsQry;
use Gedmo\SoftDeleteable\Filter\SoftDeleteableFilter;
use Mockery as m;

final class DataRetentionTest extends RepositoryTestCase
{
    private const string FROM = ' FROM ' . Entity::class . ' m';

    /** Every records query is scoped to one enabled rule. */
    private const string RULE_WHERE = 'drr.isEnabled = 1'
        . ' AND m.dataRetentionRule = :dataRetentionRuleId';

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    public function testApplyListJoins(): void
    {
        $qb = $this->createRealQb();

        // applyListJoins() omits modifyQuery(); fetchList() points the shared helper here first.
        $this->queryBuilder->modifyQuery($qb);

        $this->sut->applyListJoins($qb);

        $this->assertSame(
            'SELECT m, drr, u, cd, p' . self::FROM
            . ' LEFT JOIN m.dataRetentionRule drr LEFT JOIN m.assignedTo u'
            . ' LEFT JOIN u.contactDetails cd LEFT JOIN cd.person p',
            $qb->getDQL(),
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('listFilterProvider')]
    public function testApplyListFilters(array $data, string $expectedLeadingWhere): void
    {
        $qb = $this->createRealQb();

        $this->sut->applyListFilters($qb, RecordsQry::create($data + ['dataRetentionRuleId' => 1]));

        $this->assertSame(
            'SELECT m' . self::FROM . ' WHERE'
            . ($expectedLeadingWhere === '' ? ' ' : $expectedLeadingWhere . ' AND ')
            . self::RULE_WHERE,
            $qb->getDQL(),
        );
        $this->assertSame(1, $qb->getParameter('dataRetentionRuleId')->getValue());
    }

    public static function listFilterProvider(): \Iterator
    {
        yield 'no narrowing' => [[], ''];

        // markedForDeletion is a Y/N flag mapped to the boolean actionConfirmation column.
        yield 'marked for deletion' => [
            ['markedForDeletion' => 'Y'],
            ' m.actionConfirmation = :actionConfirmation',
        ];
        yield 'not marked for deletion' => [
            ['markedForDeletion' => 'N'],
            ' m.actionConfirmation = :actionConfirmation',
        ];

        // Deferred means the review is in the future; pending means it is due or unset.
        yield 'deferred review' => [['nextReview' => 'deferred'], ' m.nextReviewDate > :today'];
        yield 'pending review' => [
            ['nextReview' => 'pending'],
            ' (m.nextReviewDate IS NULL OR m.nextReviewDate <= :today)',
        ];

        yield 'assigned to a user' => [['assignedToUser' => '7'], ' m.assignedTo = :assignedToUser'];
        yield 'unassigned' => [['assignedToUser' => 'unassigned'], ' m.assignedTo IS NULL'];
        // 'all' is neither numeric nor 'unassigned', so no filter is applied.
        yield 'all users' => [['assignedToUser' => 'all'], ''];

        yield 'goods or psv' => [['goodsOrPsv' => 'lcat_gv'], ' m.goodsOrPsv = :goodsOrPsv'];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('markedForDeletionProvider')]
    public function testApplyListFiltersMapsMarkedForDeletionToABoolean(string $flag, int $expected): void
    {
        $qb = $this->createRealQb();

        $this->sut->applyListFilters(
            $qb,
            RecordsQry::create(['dataRetentionRuleId' => 1, 'markedForDeletion' => $flag]),
        );

        $this->assertSame($expected, $qb->getParameter('actionConfirmation')->getValue());
    }

    public static function markedForDeletionProvider(): \Iterator
    {
        yield 'yes' => ['Y', 1];
        yield 'no' => ['N', 0];
    }

    public function testRunCleanupProc(): void
    {
        $statement = m::mock(\PDOStatement::class);
        $statement->shouldReceive('execute')->andReturn(true)
            ->shouldReceive('rowCount')->andReturn(1)
            ->shouldReceive('nextRowset')->andReturn(false)
            ->shouldReceive('closeCursor')->andReturn(true);

        $this->em->expects('getConnection->getNativeConnection->prepare')
            ->with('CALL sp_dr_cleanup(2, 10, 0)')
            ->andReturn($statement);

        $this->assertTrue($this->sut->runCleanupProc(10, 2));
    }

    /**
     * Processed records are soft-deleted, so the filter has to be lifted for the query and put
     * back afterwards. The end date is widened to the end of that day.
     */
    public function testFetchAllProcessedForRule(): void
    {
        $filter = m::mock(SoftDeleteableFilter::class);
        $filter->shouldReceive('disableForEntity')->with(Entity::class);

        $filters = m::mock(FilterCollection::class);
        $filters->shouldReceive('isEnabled')->with('soft-deleteable')->andReturnTrue();
        $filters->shouldReceive('getFilter')->with('soft-deleteable')->andReturn($filter);
        $filters->shouldReceive('enable')->with('soft-deleteable');
        $this->em->shouldReceive('getFilters')->withNoArgs()->andReturn($filters);

        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_ARRAY)->andReturn(['RESULTS']);

        $result = $this->sut->fetchAllProcessedForRule(
            1,
            new \DateTime('2019-01-01 09:00:00'),
            new \DateTime('2019-01-31 09:00:00'),
        );

        $this->assertSame(['RESULTS'], $result);

        $this->assertSame(
            'SELECT m' . self::FROM
            . ' WHERE m.dataRetentionRule = :dataRetentionRuleId'
            . ' AND m.deletedDate >= :startDate AND m.deletedDate < :endDate',
            $qb->getDQL(),
        );
        $this->assertSame('2019-01-01 00:00:00', $qb->getParameter('startDate')->getValue());
        $this->assertSame('2019-02-01 00:00:00', $qb->getParameter('endDate')->getValue());
    }
}
