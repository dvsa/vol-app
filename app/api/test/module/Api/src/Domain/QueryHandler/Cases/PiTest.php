<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Cases;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\Cases\Pi;
use Dvsa\Olcs\Api\Domain\QueryHandler\Result;
use Dvsa\Olcs\Api\Domain\Repository\Pi as PiRepo;
use Dvsa\Olcs\Api\Entity\Pi\Pi as PiEntity;
use Dvsa\Olcs\Transfer\Query\Cases\Pi as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * @covers Dvsa\Olcs\Api\Domain\QueryHandler\Cases\Pi
 */
class PiTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Pi();
        $this->mockRepo('Pi', PiRepo::class);

        $this->repoMap['Pi']
            ->shouldReceive('disableSoftDeleteable')
            ->with(
                [
                    \Dvsa\Olcs\Api\Entity\Pi\Reason::class,
                    \Dvsa\Olcs\Api\Entity\Pi\PresidingTc::class,
                ]
            )
            ->once();

        parent::setUp();
    }

    /**
     * Tests an empty result is correctly dealt with
     */
    public function testHandleQueryEmptyResult(): void
    {
        $query = Qry::create(['id' => 1]);

        $this->repoMap['Pi']
            ->shouldReceive('fetchUsingCase')
            ->with($query, Query::HYDRATE_OBJECT)
            ->andReturn(null);

        $this->assertEquals([], $this->sut->handleQuery($query));
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['id' => 1]);

        $pi = m::mock(PiEntity::class);
        $pi->shouldReceive('flattenSlaTargetDates')->once()->andReturn([]);

        $this->repoMap['Pi']
            ->shouldReceive('fetchUsingCase')
            ->with($query, Query::HYDRATE_OBJECT)
            ->andReturn($pi);

        $this->assertInstanceOf(Result::class, $this->sut->handleQuery($query));
    }
}
