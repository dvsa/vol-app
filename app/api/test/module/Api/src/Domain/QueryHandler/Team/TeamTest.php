<?php

declare(strict_types=1);

/**
 * Team Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Team;

use Dvsa\Olcs\Api\Domain\QueryHandler\Team\Team as QueryHandler;
use Dvsa\Olcs\Transfer\Query\Team\Team as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Team as TeamRepo;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;

/**
 * Team Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TeamTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('Team', TeamRepo::class);
        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Query::create(['id' => 1]);

        $mockTeam = m::mock(BundleSerializableInterface::class)
            ->shouldReceive('serialize')->with(['trafficArea', 'teamPrinters' => ['printer', 'user', 'subCategory']])
            ->once()
            ->andReturn(['result' => ['foo'], 'count' => 1])
            ->getMock();

        $this->repoMap['Team']
            ->shouldReceive('fetchUsingId')
            ->with($query)
            ->once()
            ->andReturn($mockTeam)
            ->getMock();

        $this->assertSame(
            [
                'result'    => ['foo'],
                'count'     => 1,
            ],
            $this->sut->handleQuery($query)->serialize()
        );
    }
}
