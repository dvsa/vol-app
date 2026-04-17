<?php

declare(strict_types=1);

/**
 * ConditionUndertakingList Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Cases\ConditionUndertaking;

use Dvsa\Olcs\Api\Domain\QueryHandler\Cases\ConditionUndertaking\ConditionUndertakingList;
use Dvsa\Olcs\Api\Domain\Repository\ConditionUndertaking as ConditionUndertakingRepo;
use Dvsa\Olcs\Transfer\Query\Cases\ConditionUndertaking\ConditionUndertakingList as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * ConditionUndertakingList Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class ConditionUndertakingListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new ConditionUndertakingList();
        $this->mockRepo('ConditionUndertaking', ConditionUndertakingRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create([]);

        $mockResult = m::mock();
        $mockResult->shouldReceive('serialize')->once()->andReturn('foo');

        $this->repoMap['ConditionUndertaking']->shouldReceive('fetchList')
            ->with($query, m::type('integer'))
            ->andReturn([$mockResult]);

        $this->repoMap['ConditionUndertaking']->shouldReceive('fetchCount')
            ->with($query)
            ->andReturn(2);

        $result = $this->sut->handleQuery($query);
        $this->assertEquals($result['count'], 2);
        $this->assertEquals($result['result'], ['foo']);
    }
}
