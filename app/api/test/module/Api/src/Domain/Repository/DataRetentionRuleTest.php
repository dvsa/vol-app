<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\DataRetentionRule as Repo;
use Dvsa\Olcs\Api\Entity\DataRetentionRule as Entity;
use Dvsa\Olcs\Transfer\Query\DataRetention\RuleAdmin;
use Dvsa\Olcs\Transfer\Query\DataRetention\RuleList;
use Mockery as m;

final class DataRetentionRuleTest extends RepositoryTestCase
{
    protected $sut;

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('rulesProvider')]
    public function testFetchRules(string $method, array $args, string $expectedDql): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULT']);

        $paginator = m::mock();
        $paginator->shouldReceive('count')->withNoArgs()->andReturn(1);
        $this->sut->shouldReceive('getPaginator')->andReturn($paginator);

        $this->assertSame(
            ['results' => ['RESULT'], 'count' => 1],
            $this->sut->{$method}(...$args),
        );

        $this->assertSame($expectedDql, $qb->getDQL());
    }

    public static function rulesProvider(): \Iterator
    {
        // Both methods only modifyQuery() up front; withRefdata() (which joins actionType as
        // w0) runs inside buildDefaultListQuery, and that is reached only when a query is given.
        $bare = 'SELECT m FROM ' . Entity::class . ' m';
        $joined = 'SELECT m, w0 FROM ' . Entity::class . ' m LEFT JOIN m.actionType w0';
        $enabled = ' WHERE m.isEnabled = 1 AND m.deletedDate IS NULL';

        yield 'enabled rules' => ['fetchEnabledRules', [], $bare . $enabled];

        yield 'enabled review rules with a list query' => [
            'fetchEnabledRules',
            [RuleList::create(['sort' => 'id', 'order' => 'DESC']), true],
            $joined . $enabled . ' AND m.actionType = :actionType ORDER BY m.id DESC',
        ];

        yield 'all rules' => ['fetchAllRules', [], $bare];

        yield 'all rules with a list query' => [
            'fetchAllRules',
            [RuleAdmin::create(['sort' => 'id', 'order' => 'DESC'])],
            $joined . ' ORDER BY m.id DESC',
        ];
    }

    public function testFetchEnabledRulesBindsTheReviewActionType(): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULT']);

        $paginator = m::mock();
        $paginator->shouldReceive('count')->withNoArgs()->andReturn(1);
        $this->sut->shouldReceive('getPaginator')->andReturn($paginator);

        $this->sut->fetchEnabledRules(RuleList::create(['sort' => 'id', 'order' => 'DESC']), true);

        $this->assertSame('Review', $qb->getParameter('actionType')->getValue());
    }

    public function testRunProc(): void
    {
        $statement = m::mock(\PDOStatement::class);
        $statement->shouldReceive('rowCount')->andReturn(12)
            ->shouldReceive('nextRowset')
            ->shouldReceive('execute')->andReturn(true)
            ->shouldReceive('closeCursor')->andReturn(true);

        $this->em->expects('getConnection->getNativeConnection->prepare')
            ->with('CALL proc(99)')
            ->andReturn($statement);

        $this->assertTrue($this->sut->runProc('proc', 99));
    }
}
