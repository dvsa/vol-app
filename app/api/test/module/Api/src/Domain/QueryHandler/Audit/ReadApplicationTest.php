<?php

declare(strict_types=1);

/**
 * Read Application Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Audit;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\Audit\ReadApplication;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Transfer\Query\Audit\ReadApplication as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * Read Application Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ReadApplicationTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new ReadApplication();
        $this->mockRepo('ApplicationReadAudit', Repository\ApplicationReadAudit::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['id' => 111]);

        $mockRecord = m::mock();
        $mockRecord->shouldReceive('serialize')->andReturn(['foo' => 'bar']);

        $this->repoMap['ApplicationReadAudit']
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
