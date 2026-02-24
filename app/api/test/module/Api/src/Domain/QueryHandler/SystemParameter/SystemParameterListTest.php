<?php

declare(strict_types=1);

/**
 * SystemParameter List Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\SystemParameter;

use Dvsa\Olcs\Api\Domain\QueryHandler\SystemParameter\SystemParameterList as QueryHandler;
use Dvsa\Olcs\Transfer\Query\SystemParameter\SystemParameterList as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\SystemParameter as SystemParameterRepo;
use Mockery as m;
use Doctrine\ORM\Query as DoctrineQuery;

/**
 * SystemParameter List Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class SystemParameterListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('SystemParameter', SystemParameterRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Query::create(['id' => 1]);

        $mockSystemParameter = m::mock();
        $mockSystemParameter->shouldReceive('serialize')->once()->andReturn('foo');

        $this->repoMap['SystemParameter']
            ->shouldReceive('fetchList')
            ->with($query, DoctrineQuery::HYDRATE_OBJECT)
            ->once()
            ->andReturn([$mockSystemParameter])
            ->shouldReceive('fetchCount')
            ->with($query)
            ->once()
            ->andReturn(1)
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
