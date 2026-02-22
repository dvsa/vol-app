<?php

declare(strict_types=1);

/**
 * StayList Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Cases\Hearing;

use Dvsa\Olcs\Api\Domain\QueryHandler\Cases\Hearing\StayList;
use Dvsa\Olcs\Api\Domain\Repository\Hearing as StayRepo;
use Dvsa\Olcs\Transfer\Query\Cases\Hearing\StayList as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * StayList Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class StayListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new StayList();
        $this->mockRepo('Stay', StayRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create([]);

        $mockResult = m::mock();
        $mockResult->shouldReceive('serialize')->once()->andReturn('foo');

        $this->repoMap['Stay']->shouldReceive('fetchList')
            ->with($query, m::type('integer'))
            ->andReturn([$mockResult]);

        $this->repoMap['Stay']->shouldReceive('fetchCount')
            ->with($query)
            ->andReturn(2);

        $result = $this->sut->handleQuery($query);
        $this->assertEquals($result['count'], 2);
        $this->assertEquals($result['result'], ['foo']);
    }
}
