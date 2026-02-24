<?php

declare(strict_types=1);

/**
 * StatementList Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Cases\Statement;

use Dvsa\Olcs\Api\Domain\QueryHandler\Cases\Statement\StatementList;
use Dvsa\Olcs\Api\Domain\Repository\Statement as StatementRepo;
use Dvsa\Olcs\Transfer\Query\Cases\Statement\StatementList as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * StatementList Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class StatementListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new StatementList();
        $this->mockRepo('Statement', StatementRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create([]);

        $mockResult = m::mock();
        $mockResult->shouldReceive('serialize')->once()->andReturn('foo');

        $this->repoMap['Statement']->shouldReceive('fetchList')
            ->with($query, m::type('integer'))
            ->andReturn([$mockResult]);

        $this->repoMap['Statement']->shouldReceive('fetchCount')
            ->with($query)
            ->andReturn(2);

        $result = $this->sut->handleQuery($query);
        $this->assertEquals($result['count'], 2);
        $this->assertEquals($result['result'], ['foo']);
    }
}
