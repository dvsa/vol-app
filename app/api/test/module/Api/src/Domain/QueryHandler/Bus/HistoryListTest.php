<?php

declare(strict_types=1);

/**
 * History List Test
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Bus;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\Bus\HistoryList;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Transfer\Query\Bus\HistoryList as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * History List Test
 */
class HistoryListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new HistoryList();
        $this->mockRepo('BusRegHistory', Repository\BusRegHistory::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['id' => 111]);

        $mockRecord = m::mock();
        $mockRecord->shouldReceive('serialize')->andReturn(['foo' => 'bar']);

        $this->repoMap['BusRegHistory']
            ->shouldReceive('disableSoftDeleteable')
            ->once()
            ->shouldReceive('fetchList')
            ->once()
            ->with($query, Query::HYDRATE_OBJECT)
            ->andReturn([$mockRecord])
            ->shouldReceive('fetchCount')
            ->once()
            ->with($query)
            ->andReturn(1);

        $expected = [
            'result' => [
                ['foo' => 'bar']
            ],
            'count' => 1
        ];

        $this->assertEquals($expected, $this->sut->handleQuery($query));
    }
}
