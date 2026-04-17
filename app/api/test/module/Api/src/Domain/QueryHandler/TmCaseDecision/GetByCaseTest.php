<?php

declare(strict_types=1);

/**
 * GetByCase Test
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\TmCaseDecision;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\TmCaseDecision\GetByCase;
use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use Dvsa\Olcs\Api\Domain\Repository\TmCaseDecision as TmCaseDecisionRepo;
use Dvsa\Olcs\Transfer\Query\TmCaseDecision\GetByCase as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;

/**
 * GetByCase Test
 */
class GetByCaseTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new GetByCase();
        $this->mockRepo('TmCaseDecision', TmCaseDecisionRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['case' => 1]);

        $this->repoMap['TmCaseDecision']->shouldReceive('fetchLatestUsingCase')
            ->with($query)
            ->andReturn(
                m::mock(BundleSerializableInterface::class)
                    ->shouldReceive('serialize')
                    ->andReturn(['foo'])
                    ->getMock()
            );

        $result = $this->sut->handleQuery($query);
        $this->assertEquals(['foo'], $result->serialize());
    }

    public function testHandleQueryNoResult(): void
    {
        $query = Qry::create(['case' => 1]);

        $this->repoMap['TmCaseDecision']->shouldReceive('fetchLatestUsingCase')
            ->with($query)
            ->andReturn(false);

        $result = $this->sut->handleQuery($query);
        $this->assertEquals([], $result);
    }
}
