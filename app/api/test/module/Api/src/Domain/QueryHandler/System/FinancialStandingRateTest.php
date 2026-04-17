<?php

declare(strict_types=1);

/**
 * FinancialStandingRate Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\System;

use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\QueryHandler\System\FinancialStandingRate as QueryHandler;
use Dvsa\Olcs\Transfer\Query\System\FinancialStandingRate as Qry;
use Dvsa\Olcs\Api\Entity\System\FinancialStandingRate as Entity;
use Dvsa\Olcs\Api\Domain\Repository\FinancialStandingRate as Repo;

/**
 * FinancialStandingRate Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class FinancialStandingRateTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('FinancialStandingRate', Repo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $id = 69;

        $mockRate = m::mock(Entity::class)
            ->shouldReceive('serialize')
            ->andReturn(
                [
                    'id' => $id,
                    'foo' => 'bar',
                ]
            )
            ->getMock();

        $query = Qry::create(['id' => $id]);

        $this->repoMap['FinancialStandingRate']
            ->shouldReceive('fetchUsingId')
            ->with($query)
            ->once()
            ->andReturn($mockRate);

        $result = $this->sut->handleQuery($query);

        $expected = [
            'id' => $id,
            'foo' => 'bar',
        ];

        $this->assertEquals($expected, $result->serialize());
    }
}
