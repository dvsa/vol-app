<?php

declare(strict_types=1);

/**
 * Venue Bundle Test
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Bookmark;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\Bookmark\VenueBundle;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Venue as Repo;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\VenueBundle as Qry;
use Dvsa\Olcs\Api\Entity\Venue as Entity;

/**
 * Venue Bundle Test
 */
class VenueBundleTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new VenueBundle();
        $this->mockRepo('Venue', Repo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $id = 99;
        $bundle = [];

        $query = Qry::create(['id' => $id, 'bundle' => $bundle]);

        /** @var Entity $entity */
        $entity = m::mock(Entity::class)
            ->shouldReceive('serialize')
            ->once()
            ->andReturn(['foo' => 'bar'])
            ->getMock();

        $this->repoMap['Venue']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($entity);

        $this->assertEquals(['foo' => 'bar'], $this->sut->handleQuery($query)->serialize());
    }
}
