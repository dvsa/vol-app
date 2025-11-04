<?php

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\DocTemplate;

use Dvsa\Olcs\Api\Domain\QueryHandler\DocTemplate\GetList as QueryHandler;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * GetListTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class GetListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('DocTemplate', \Dvsa\Olcs\Api\Domain\Repository\DocTemplate::class);

        parent::setUp();
    }

    public function testHandleQuery()
    {
        $query = m::mock(\Dvsa\Olcs\Transfer\Query\QueryInterface::class);
        $mockResult = m::mock(\Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface::class);
        $mockResult->shouldReceive('serialize')->with([0 => 'document', 1 => 'letterType'])->once()->andReturn(['foo' => 'bar']);

        $this->repoMap['DocTemplate']
            ->shouldReceive('fetchList')->with($query, \Doctrine\ORM\Query::HYDRATE_OBJECT)->once()
            ->andReturn([$mockResult]);

        $expected = [
            'result' => [
                ['foo' => 'bar']
            ],
            'count' => 1,
        ];

        $this->assertSame($expected, $this->sut->handleQuery($query));
    }
}
