<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\GracePeriod;

use Dvsa\Olcs\Api\Domain\QueryHandler\GracePeriod\GracePeriod;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Transfer\Query\GracePeriod\GracePeriod as GracePeriodsQuery;
use Mockery as m;

/**
 * Grace Periods Test
 *
 * @author Joshua Curtis <josh.curtis@valtech.co.uk>
 */
class GracePeriodTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new GracePeriod();
        $this->mockRepo('GracePeriod', \Dvsa\Olcs\Api\Domain\Repository\GracePeriod::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = GracePeriodsQuery::create(['id' => 1]);

        $mockEntity = m::mock(\Dvsa\Olcs\Api\Entity\Licence\GracePeriod::class);
        $mockEntity->shouldReceive('serialize')->once()->andReturn(['unitKey' => 'unitVal']);

        $this->repoMap['GracePeriod']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($mockEntity);

        /** @var \Dvsa\Olcs\Api\Domain\QueryHandler\Result $actual */
        $actual = $this->sut->handleQuery($query);

        static::assertEquals(
            [
                'unitKey' => 'unitVal',
            ],
            $actual->serialize()
        );
    }
}
