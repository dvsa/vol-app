<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Audit;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\Audit\ReadIrhpApplication;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Transfer\Query\Audit\ReadIrhpApplication as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * Read Irhp Application Test
 */
class ReadIrhpApplicationTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new ReadIrhpApplication();
        $this->mockRepo('IrhpApplicationReadAudit', Repository\IrhpApplicationReadAudit::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['id' => 111]);

        $mockRecord = m::mock();
        $mockRecord->shouldReceive('serialize')->andReturn(['foo' => 'bar']);

        $this->repoMap['IrhpApplicationReadAudit']
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
            'results' => [
                ['foo' => 'bar']
            ],
            'count' => 1
        ];

        $this->assertEquals($expected, $this->sut->handleQuery($query));
    }
}
