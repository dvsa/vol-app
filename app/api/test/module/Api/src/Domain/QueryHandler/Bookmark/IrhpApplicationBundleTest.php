<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\IrhpApplicationBundle as Qry;
use Dvsa\Olcs\Api\Domain\QueryHandler\Bookmark\IrhpApplicationBundle;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as Repo;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as Entity;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * IrhpApplicationBundleTest
 */
class IrhpApplicationBundleTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new IrhpApplicationBundle();
        $this->mockRepo('IrhpApplication', Repo::class);

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

        $this->repoMap['IrhpApplication']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($entity);

        $this->assertEquals(['foo' => 'bar'], $this->sut->handleQuery($query));
    }
}
