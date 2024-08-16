<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Team;

use Doctrine\ORM\Query as DoctrineQuery;
use Dvsa\Olcs\Api\Domain\QueryHandler\Result;
use Dvsa\Olcs\Api\Domain\QueryHandler\Team\TeamListData as QueryHandler;
use Dvsa\Olcs\Transfer\Query\Team\TeamListData as Query;
use Dvsa\Olcs\Api\Domain\Query\Team\TeamListByTrafficArea;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Team as TeamRepo;
use Mockery as m;

/**
 * @see QueryHandler
 */
class TeamListDataTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('Team', TeamRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Query::create([]);

        $userData = [
            'dataAccess' => [
                'canAccessAll' => true,
            ],
        ];

        $this->expectedUserDataCacheCall($userData);

        $mockTeam = m::mock();
        $mockTeam->shouldReceive('serialize')->once()->andReturn('foo');

        $this->repoMap['Team']
            ->shouldReceive('fetchList')
            ->with($query, DoctrineQuery::HYDRATE_OBJECT)
            ->once()
            ->andReturn([$mockTeam])
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

    public function testRedirectWhenNotFullAccess(): void
    {
        $trafficAreas = ['B','C'];

        $initialQueryData = [
            'sort' => 'id',
            'order' => 'ASC',
            'sortWhitelist' => [],
        ];

        $teamByTaQueryData = [
            'sort' => 'id',
            'order' => 'ASC',
            'sortWhitelist' => [],
            'trafficAreas' => $trafficAreas,
        ];

        $query = Query::create($initialQueryData);

        $userData = [
            'dataAccess' => [
                'canAccessAll' => false,
                'trafficAreas' => $trafficAreas,
            ],
        ];

        $queryResult = new Result();
        $this->expectedUserDataCacheCall($userData);
        $this->expectedQuery(TeamListByTrafficArea::class, $teamByTaQueryData, $queryResult);
        $this->assertSame($queryResult, $this->sut->handleQuery($query));
    }
}
