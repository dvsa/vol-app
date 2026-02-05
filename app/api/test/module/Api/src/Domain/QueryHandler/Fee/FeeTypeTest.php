<?php

declare(strict_types=1);

/**
 * Fee Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Fee;

use Dvsa\Olcs\Api\Domain\QueryHandler\Fee\FeeType as QueryHandler;
use Dvsa\Olcs\Api\Domain\QueryHandler\Result;
use Dvsa\Olcs\Api\Domain\Repository\FeeType as FeeTypeRepo;
use Dvsa\Olcs\Api\Entity\Fee\FeeType as Entity;
use Dvsa\Olcs\Transfer\Query\Fee\Fee as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * FeeType Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class FeeTypeTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('FeeType', FeeTypeRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['id' => 69]);

        $mockFeeType = m::mock(Entity::class)
            ->shouldReceive('isShowQuantity')
            ->andReturn(true)
            ->once()
            ->getMock();

        $this->repoMap['FeeType']
            ->shouldReceive('fetchUsingId')
            ->with($query)
            ->once()
            ->andReturn($mockFeeType);

        $result = $this->sut->handleQuery($query);

        $this->assertInstanceOf(Result::class, $result);

        $mockFeeType
            ->shouldReceive('serialize')
            ->once()
            ->andReturn(['id' => 69]);

        $expected = ['id' => 69, 'showQuantity' => true];

        $this->assertEquals($expected, $result->serialize());
    }
}
