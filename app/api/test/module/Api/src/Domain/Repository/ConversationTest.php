<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\Repository\Conversation;
use Mockery as m;

class ConversationTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(Conversation::class);
    }

    public function testApplyOrderForListing(): void
    {
        $qb = m::mock(QueryBuilder::class);
        $roleNames = ['role1', 'role2'];

        $subQuery = m::mock(QueryBuilder::class);
        $this->em->shouldReceive('createQueryBuilder')
            ->twice()
            ->andReturn($subQuery);

        $subQuery->shouldReceive('select')
            ->twice()
            ->andReturnSelf()
            ->shouldReceive('from')
            ->twice()
            ->andReturnSelf()
            ->shouldReceive('join')
            ->times(4)
            ->andReturnSelf()
            ->shouldReceive('where')
            ->twice()
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->twice()
            ->andReturnSelf()
            ->shouldReceive('getDQL')
            ->twice()
            ->andReturn('SUBQUERY');

        $qb->shouldReceive('leftJoin')
            ->times(3)
            ->andReturnSelf()
            ->shouldReceive('addSelect')
            ->twice()
            ->andReturnSelf()
            ->shouldReceive('groupBy')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('addOrderBy')
            ->times(3)
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->twice()
            ->andReturnSelf()
            ->shouldReceive('expr')
            ->times(5)
            ->andReturn(m::mock()->shouldReceive('in', 'eq', 'orX', 'exists')->getMock());

        $result = $this->sut->applyOrderForListing($qb, $roleNames);

        $this->assertSame($qb, $result);
    }

    public function testFilterByStatuses(): void
    {
        $qb = m::mock(QueryBuilder::class);
        $statuses = ['open', 'closed'];

        $qb->shouldReceive('andWhere')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('expr')
            ->once()
            ->andReturn(m::mock()->shouldReceive('orX')->andReturnSelf()->shouldReceive('addMultiple')->getMock());

        $result = $this->sut->filterByStatuses($qb, $statuses);

        $this->assertSame($qb, $result);
    }
}
