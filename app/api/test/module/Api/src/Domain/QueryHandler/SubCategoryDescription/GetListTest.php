<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\SubCategoryDescription;

use Dvsa\Olcs\Api\Domain\QueryHandler\SubCategoryDescription\GetList as QueryHandler;
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
        $this->mockRepo('SubCategoryDescription', \Dvsa\Olcs\Api\Domain\Repository\SubCategoryDescription::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = m::mock(\Dvsa\Olcs\Transfer\Query\QueryInterface::class);
        $mockResult = m::mock(\Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface::class);
        $mockResult->shouldReceive('serialize')->with([])->once()->andReturn(['foo' => 'bar']);

        $this->repoMap['SubCategoryDescription']
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
