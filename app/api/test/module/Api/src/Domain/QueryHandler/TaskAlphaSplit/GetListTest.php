<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\TaskAlphaSplit;

use Dvsa\Olcs\Api\Domain\QueryHandler\TaskAlphaSplit\GetList as QueryHandler;
use Dvsa\Olcs\Transfer\Query\TaskAlphaSplit\GetList as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use Mockery as m;

/**
 * TaskAlphaSplit GetListTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class GetListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('TaskAlphaSplit', \Dvsa\Olcs\Api\Domain\Repository\TaskAlphaSplit::class);
        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Query::create([]);

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

        $this->repoMap['TaskAlphaSplit']->shouldReceive('fetchList')
            ->with($query, \Doctrine\ORM\Query::HYDRATE_OBJECT)->once()->andReturn([$mockTas]);
        $this->repoMap['TaskAlphaSplit']->shouldReceive('fetchCount')->with($query)->once()->andReturn(1304);
        $this->repoMap['TaskAlphaSplit']->shouldReceive('hasRows')->with($query)->once()->andReturn(false);

        $this->assertSame(
            [
                'result' => [
                    ['foo' => 'bar']
                ],
                'count' => 1304,
                'count-unfiltered' => false,
            ],
            $this->sut->handleQuery($query)
        );
    }
}
