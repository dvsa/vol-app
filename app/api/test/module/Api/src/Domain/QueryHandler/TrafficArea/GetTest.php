<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\TrafficArea;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\TrafficArea\Get as QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\TrafficArea as Repo;
use Dvsa\Olcs\Transfer\Query\TrafficArea\Get as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TrafficAreaEntity;

/**
 * Traffic Area test
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class GetTest extends QueryHandlerTestCase
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

        $trafficArea = m::mock(TrafficAreaEntity::class);
        $trafficArea->shouldReceive('serialize')->with(
            ['trafficAreaEnforcementAreas' => ['enforcementArea']]
        )->once()->andReturn(['SERIALIZED']);

        $this->repoMap['TrafficArea']->shouldReceive('fetchUsingId')->with($query)->andReturn($trafficArea);

        $result = $this->sut->handleQuery($query);

        $this->assertSame(['SERIALIZED'], $result->serialize());
    }
}
