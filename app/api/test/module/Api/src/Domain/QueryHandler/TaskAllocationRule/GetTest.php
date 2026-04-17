<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\TaskAllocationRule;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use Dvsa\Olcs\Api\Domain\QueryHandler\TaskAllocationRule\Get as QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Transfer\Query\TaskAllocationRule\Get as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

class GetTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo(Repository\TaskAllocationRule::class, Repository\TaskAllocationRule::class);
        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Query::create(['id' => 1304]);

        $mockTas = m::mock(BundleSerializableInterface::class)
            ->shouldReceive('serialize')
            ->with(
                [
                    'category',
                    'subCategory',
                    'team',
                    'user' => ['contactDetails' => ['person']],
                    'trafficArea',
                    'taskAlphaSplits',
                ]
            )
            ->once()
            ->andReturn(['foo' => 'bar'])
            ->getMock();

        $this->repoMap[Repository\TaskAllocationRule::class]->shouldReceive('fetchUsingId')->with($query)->once()->andReturn($mockTas);

        $this->assertSame(
            ['foo' => 'bar'],
            $this->sut->handleQuery($query)->serialize()
        );
    }
}
