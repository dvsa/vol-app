<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\GracePeriod;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\GracePeriod\GracePeriods;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Transfer\Query\GracePeriod\GracePeriods as GracePeriodsQuery;
use Mockery as m;

/**
 * Grace Periods Test
 *
 * @author Joshua Curtis <josh.curtis@valtech.co.uk>
 */
class GracePeriodsTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new GracePeriods();
        $this->mockRepo('GracePeriod', \Dvsa\Olcs\Api\Domain\Repository\GracePeriod::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = GracePeriodsQuery::create(['licence' => 1]);

        $mockEntity = m::mock(\Dvsa\Olcs\Api\Entity\Licence\GracePeriod ::class);
        $mockEntity->shouldReceive('serialize')->once()->andReturn('unit_SERIALIZED');

        $this->repoMap['GracePeriod']
            ->shouldReceive('fetchList')
            ->with($query, Query::HYDRATE_OBJECT)
            ->andReturn([$mockEntity])
            ->shouldReceive('fetchCount')
            ->with($query)
            ->andReturn('unit_Count');

        $actual = $this->sut->handleQuery($query);

        static::assertEquals(
            [
                'result' => ['unit_SERIALIZED'],
                'count' => 'unit_Count',
            ],
            $actual
        );
    }
}
