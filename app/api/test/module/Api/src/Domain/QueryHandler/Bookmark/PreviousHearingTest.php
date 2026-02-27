<?php

declare(strict_types=1);

/**
 * Previous Hearing Bundle Test
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Bookmark;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\Bookmark\PreviousHearing;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\PiHearing as Repo;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\PreviousHearingBundle as Qry;
use Dvsa\Olcs\Api\Entity\Pi\PiHearing as Entity;

/**
 * Previous Hearing Bundle Test
 */
class PreviousHearingTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new PreviousHearing();
        $this->mockRepo('PiHearing', Repo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $pi = 99;
        $hearingDate = '2015-03-06 20:00:00';
        $bundle = [];

        $query = Qry::create(['pi' => $pi, 'hearingDate' => $hearingDate, 'bundle' => $bundle]);

        /** @var Entity $entity */
        $entity = m::mock(Entity::class)
            ->shouldReceive('serialize')
            ->once()
            ->andReturn(['foo' => 'bar'])
            ->getMock();

        $this->repoMap['PiHearing']->shouldReceive('fetchPreviousHearing')
            ->with($pi, m::type(\DateTime::class))
            ->andReturn($entity);

        $this->assertEquals(['foo' => 'bar'], $this->sut->handleQuery($query)->serialize());
    }

    public function testHandleQueryEmpty(): void
    {
        $pi = 99;
        $hearingDate = '2015-03-06 20:00:00';
        $bundle = [];

        $query = Qry::create(['pi' => $pi, 'hearingDate' => $hearingDate, 'bundle' => $bundle]);

        $this->repoMap['PiHearing']->shouldReceive('fetchPreviousHearing')
            ->with($pi, m::type(\DateTime::class))
            ->andReturn(null);

        $this->assertNull($this->sut->handleQuery($query));
    }
}
