<?php

declare(strict_types=1);

/**
 * FinancialStandingRateList Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\System;

use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\QueryHandler\System\FinancialStandingRateList as QueryHandler;
use Dvsa\Olcs\Transfer\Query\System\FinancialStandingRateList as Qry;
use Dvsa\Olcs\Api\Entity\System\FinancialStandingRate as Entity;
use Dvsa\Olcs\Api\Domain\Repository\FinancialStandingRate as Repo;

/**
 * FinancialStandingRateList Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class FinancialStandingRateListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('FinancialStandingRate', Repo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $mockRate1 = m::mock(Entity::class)
            ->shouldReceive('serialize')
            ->andReturn(['id' => 69])
            ->getMock();

        $mockRate2 = m::mock(Entity::class)
            ->shouldReceive('serialize')
            ->andReturn(['id' => 99])
            ->getMock();

        $query = Qry::create([]);

        $this->repoMap['FinancialStandingRate']
            ->shouldReceive('fetchList')
            ->with($query, 1)
            ->once()
            ->andReturn([$mockRate1, $mockRate2])
            ->shouldReceive('fetchCount')
            ->with($query)
            ->once()
            ->andReturn(2);

        $result = $this->sut->handleQuery($query);

        $expected = [
            'result' => [
                ['id' => 69],
                ['id' => 99],
            ],
            'count' => 2,
        ];

        $this->assertEquals($expected, $result);
    }
}
