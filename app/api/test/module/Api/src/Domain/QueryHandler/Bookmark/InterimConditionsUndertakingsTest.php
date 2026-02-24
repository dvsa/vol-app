<?php

declare(strict_types=1);

/**
 * InterimConditionsUndertakings Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Bookmark;

use Doctrine\Common\Collections\Criteria;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\Bookmark\InterimConditionsUndertakings;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Application as Repo;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\InterimConditionsUndertakings as Qry;
use Dvsa\Olcs\Api\Entity\Application\Application as Entity;

/**
 * InterimConditionsUndertakings Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class InterimConditionsUndertakingsTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new InterimConditionsUndertakings();
        $this->mockRepo('Application', Repo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(
            [
                'id' => 111,
                'attachedTo' => 'ATTACHED',
                'conditionType' => 'CONDITION'
            ]
        );

        /** @var Entity $entity */
        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('serialize')
            ->andReturnUsing(
                function ($bundle) {

                    $this->assertInstanceOf(Criteria::class, $bundle['licence']['conditionUndertakings']['criteria']);

                    return ['id' => 111];
                }
            );

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($entity)
            ->shouldReceive('getRefdataReference')
            ->with('ATTACHED')
            ->andReturn(m::mock())
            ->shouldReceive('getRefdataReference')
            ->with('CONDITION')
            ->andReturn(m::mock());

        $this->assertEquals(['id' => 111], $this->sut->handleQuery($query));
    }
}
