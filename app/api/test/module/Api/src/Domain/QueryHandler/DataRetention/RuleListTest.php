<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\DataRetention;

use Dvsa\Olcs\Api\Domain\Repository\DataRetentionRule as DataRetentionRuleRepo;
use Dvsa\Olcs\Api\Domain\QueryHandler\DataRetention\RuleList as QueryHandler;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Transfer\Query\DataRetention\RuleList as Query;
use Doctrine\ORM\Query as DoctrineQuery;
use Mockery as m;

/**
 * Rule List Test
 */
class RuleListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('DataRetentionRule', DataRetentionRuleRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Query::create(['id' => 1]);

        $mockRuleList = m::mock();
        $mockRuleList->shouldReceive('serialize')->once()->andReturn('foo');

        $this->repoMap['DataRetentionRule']
            ->shouldReceive('fetchAllRules')
            ->with($query)
            ->once()
            ->andReturn(
                [
                    'results' => [$mockRuleList],
                    'count' => 1,
                ]
            )
            ->getMock();

        $this->assertSame(
            [
                'result'    => ['foo'],
                'count'     => 1,
            ],
            $this->sut->handleQuery($query)
        );
    }
}
