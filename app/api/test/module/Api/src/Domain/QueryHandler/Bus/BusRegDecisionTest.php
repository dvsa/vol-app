<?php

declare(strict_types=1);

/**
 * BusReg Decision Test
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Bus;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\Bus\BusRegDecision;
use Dvsa\Olcs\Api\Entity\Bus\BusReg;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Bus as BusRepo;
use Dvsa\Olcs\Transfer\Query\Bus\BusRegDecision as Qry;
use Dvsa\Olcs\Api\Domain\QueryHandler\Result;

/**
 * BusReg Decision Test
 */
class BusRegDecisionTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new BusRegDecision();
        $this->mockRepo('Bus', BusRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['id' => 111]);

        $bus = m::mock(BusReg::class);
        $bus->shouldReceive('serialize')->once()->andReturn(['foo']);
        $bus->shouldReceive('getDecision')->once()->andReturn(['bar']);
        $bus->shouldReceive('isGrantable')->once()->andReturn(true);

        $this->repoMap['Bus']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($bus);

        /** @var Result $result */
        $result = $this->sut->handleQuery($query);

        $this->assertEquals(['foo', 'decision' => ['bar'], 'isGrantable' => true], $result->serialize());
    }
}
