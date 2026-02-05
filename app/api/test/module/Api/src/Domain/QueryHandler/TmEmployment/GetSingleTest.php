<?php

declare(strict_types=1);

/**
 * GetSingleTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\TmEmployment;

use Dvsa\Olcs\Api\Domain\QueryHandler\TmEmployment\GetSingle as QueryHandler;
use Dvsa\Olcs\Transfer\Query\TmEmployment\GetSingle as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * GetSingleTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class GetSingleTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('TmEmployment', \Dvsa\Olcs\Api\Domain\Repository\TmEmployment::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Query::create(['id' => 1066]);

        $mockTmEmployment = m::mock(\Dvsa\Olcs\Api\Entity\Tm\TmEmployment::class);
        $mockTmEmployment->shouldReceive('serialize')->once()->andReturn(['foo' => 'bar']);

        $this->repoMap['TmEmployment']->shouldReceive('fetchUsingId')->with($query)->once()
            ->andReturn($mockTmEmployment);

        $result = $this->sut->handleQuery($query)->serialize();

        $this->assertEquals(['foo' => 'bar'], $result);
    }
}
