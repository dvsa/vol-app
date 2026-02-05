<?php

declare(strict_types=1);

/**
 * Traffic Area list test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\TrafficArea;

use Dvsa\Olcs\Api\Domain\QueryHandler\Result;
use Dvsa\Olcs\Transfer\Query\TrafficArea\TrafficAreaInternalList;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\TrafficArea\TrafficAreaList as QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\TrafficArea as Repo;
use Dvsa\Olcs\Transfer\Query\TrafficArea\TrafficAreaList as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TrafficAreaEntity;

/**
 * Traffic Area list test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TrafficAreaListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('TrafficArea', Repo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Query::create(['QUERY']);

        $userData = ['isInternal' => false];
        $this->expectedUserDataCacheCall($userData);

        $trafficArea = m::mock(TrafficAreaEntity::class);
        $trafficArea->shouldReceive('serialize')->once()->andReturn('SERIALIZED');

        $this->repoMap['TrafficArea']->shouldReceive('fetchList')
            ->with($query, \Doctrine\ORM\Query::HYDRATE_OBJECT)->andReturn([$trafficArea]);
        $this->repoMap['TrafficArea']->shouldReceive('fetchCount')->with($query)->andReturn('COUNT');

        $result = $this->sut->handleQuery($query);

        $this->assertSame(['SERIALIZED'], $result['result']);
        $this->assertSame('COUNT', $result['count']);
    }

    public function testRedirectForInternalUser(): void
    {
        $query = Query::create([]);
        $trafficAreas = ['B','C'];
        $userData = [
            'isInternal' => true,
            'dataAccess' => [
                'trafficAreas' => $trafficAreas
            ]
        ];

        $queryResult = new Result();
        $this->expectedUserDataCacheCall($userData);
        $this->expectedQuery(TrafficAreaInternalList::class, ['trafficAreas' => $trafficAreas], $queryResult);
        $this->assertSame($queryResult, $this->sut->handleQuery($query));
    }
}
