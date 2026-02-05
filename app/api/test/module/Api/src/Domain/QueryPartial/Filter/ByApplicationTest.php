<?php

declare(strict_types=1);

/**
 * Filter By Application Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryPartial\Filter;

use Dvsa\Olcs\Api\Domain\QueryPartial\Filter\ByApplication;
use Dvsa\OlcsTest\Api\Domain\QueryPartial\QueryPartialTestCase;
use Mockery as m;

/**
 * Filter By Application Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ByApplicationTest extends QueryPartialTestCase
{
    public function setUp(): void
    {
        $this->sut = new ByApplication();

        parent::setUp();
    }

    public function testModifyQuery(): void
    {
        $applicationId = 69;

        $expr = m::mock();

        $this->qb->shouldReceive('getRootAliases')
            ->andReturn(['a'])
            ->shouldReceive('andWhere')
            ->once()
            ->with($expr)
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->once()
            ->with('applicationId', 69)
            ->andReturnSelf();

        $this->qb->shouldReceive('expr->eq')
            ->with('a.application', ':applicationId')
            ->andReturn($expr);

        $this->sut->modifyQuery($this->qb, [$applicationId]);
    }
}
