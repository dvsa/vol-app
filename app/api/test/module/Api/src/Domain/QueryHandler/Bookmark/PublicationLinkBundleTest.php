<?php

declare(strict_types=1);

/**
 * PublicationLink Bundle Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Bookmark;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\Bookmark\PublicationLinkBundle;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\PublicationLink as Repo;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\PublicationLinkBundle as Qry;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink as Entity;

/**
 * PublicationLink Bundle Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PublicationLinkBundleTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new PublicationLinkBundle();
        $this->mockRepo('PublicationLink', Repo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['busReg' => 111, 'bundle' => ['foo' => ['bar']]]);

        /** @var Entity $entity */
        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('serialize')
            ->with(['foo' => ['bar']])
            ->andReturn(['id' => 111]);

        $this->repoMap['PublicationLink']->shouldReceive('fetchByBusRegId')
            ->with(111)
            ->andReturn([$entity]);

        $this->assertEquals(['Results' => [['id' => 111]]], $this->sut->handleQuery($query));
    }
}
