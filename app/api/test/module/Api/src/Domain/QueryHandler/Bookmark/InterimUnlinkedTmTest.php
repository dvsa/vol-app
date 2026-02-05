<?php

declare(strict_types=1);

/**
 * InterimUnlinkedTm Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Bookmark;

use Doctrine\Common\Collections\Criteria;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\Bookmark\InterimUnlinkedTm;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Application as Repo;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\InterimUnlinkedTm as Qry;
use Dvsa\Olcs\Api\Entity\Application\Application as Entity;

/**
 * InterimUnlinkedTm Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class InterimUnlinkedTmTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new InterimUnlinkedTm();
        $this->mockRepo('Application', Repo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['id' => 111]);

        /** @var Entity $entity */
        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('serialize')
            ->with([])
            ->andReturn(['id' => 111]);

        $bundle = [
            'transportManager' => [
                'homeCd' => [
                    'person'
                ]
            ]
        ];

        $tm1 = m::mock();
        $tm1->shouldReceive('serialize')
            ->with($bundle)
            ->andReturn('foo');
        $tm2 = m::mock();
        $tm2->shouldReceive('serialize')
            ->with($bundle)
            ->andReturn('bar');

        $entity->shouldReceive('getTransportManagers->matching')
            ->with(m::type(Criteria::class))
            ->andReturn([$tm1, $tm2]);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($entity);

        $this->assertEquals(['id' => 111, 'transportManagers' => ['foo', 'bar']], $this->sut->handleQuery($query));
    }
}
