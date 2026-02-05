<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\TaskAlphaSplit;

use Dvsa\Olcs\Api\Domain\QueryHandler\TaskAlphaSplit\Get as QueryHandler;
use Dvsa\Olcs\Transfer\Query\TaskAlphaSplit\Get as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use Mockery as m;

/**
 * TaskAlphaSplit GetTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class GetTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('TaskAlphaSplit', \Dvsa\Olcs\Api\Domain\Repository\TaskAlphaSplit::class);
        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Query::create(['id' => 1304]);

        $mockTas = m::mock(BundleSerializableInterface::class)
            ->shouldReceive('serialize')
            ->with(
                [
                    'taskAllocationRule',
                    'user' => ['contactDetails' => ['person']],
                ]
            )
            ->once()
            ->andReturn(['foo' => 'bar'])
            ->getMock();

        $this->repoMap['TaskAlphaSplit']->shouldReceive('fetchUsingId')->with($query)->once()->andReturn($mockTas);

        $this->assertSame(
            ['foo' => 'bar'],
            $this->sut->handleQuery($query)->serialize()
        );
    }
}
