<?php

declare(strict_types=1);

/**
 * InterimOperatingCentres Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Bookmark;

use Doctrine\Common\Collections\Criteria;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\Bookmark\InterimOperatingCentres;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Application as Repo;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\InterimOperatingCentres as Qry;
use Dvsa\Olcs\Api\Entity\Application\Application as Entity;

/**
 * InterimOperatingCentres Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class InterimOperatingCentresTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new InterimOperatingCentres();
        $this->mockRepo('Application', Repo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['id' => 111]);

        /** @var Entity $entity */
        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('serialize')
            ->with(['licence'])
            ->andReturn(['id' => 111]);

        $bundle = [
            'operatingCentre' => [
                'address',
                'conditionUndertakings' => [
                    'conditionType',
                    'attachedTo',
                    'licence',
                    'application',
                    'licConditionVariation'
                ]
            ]
        ];

        $oc1 = m::mock();
        $oc1->shouldReceive('serialize')
            ->with($bundle)
            ->andReturn('foo');
        $oc2 = m::mock();
        $oc2->shouldReceive('serialize')
            ->with($bundle)
            ->andReturn('bar');

        $entity->shouldReceive('getOperatingCentres->matching')
            ->with(m::type(Criteria::class))
            ->andReturn([$oc1, $oc2]);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($entity);

        $this->assertEquals(
            [
                'id' => 111,
                'operatingCentres' => ['foo', 'bar'],
            ],
            $this->sut->handleQuery($query)
        );
    }
}
