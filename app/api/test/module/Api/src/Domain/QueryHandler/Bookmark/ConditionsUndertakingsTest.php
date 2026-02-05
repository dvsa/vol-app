<?php

declare(strict_types=1);

/**
 * ConditionsUndertakings Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Bookmark;

use Doctrine\Common\Collections\Criteria;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\Bookmark\ConditionsUndertakings;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Licence as Repo;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\ConditionsUndertakings as Qry;
use Dvsa\Olcs\Api\Entity\Licence\Licence as Entity;

/**
 * ConditionsUndertakings Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ConditionsUndertakingsTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new ConditionsUndertakings();
        $this->mockRepo('Licence', Repo::class);

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

        $bundle = [
            'attachedTo',
            'conditionType'
        ];

        $cu1 = m::mock();
        $cu1->shouldReceive('serialize')
            ->with($bundle)
            ->andReturn('foo');
        $cu2 = m::mock();
        $cu2->shouldReceive('serialize')
            ->with($bundle)
            ->andReturn('bar');

        /** @var Entity $application */
        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('serialize')
            ->with([])
            ->andReturn(['id' => 111]);

        $entity->shouldReceive('getConditionUndertakings->matching')
            ->once()
            ->with(m::type(Criteria::class))
            ->andReturn([$cu1, $cu2]);

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($entity)
            ->shouldReceive('getRefdataReference')
            ->with('ATTACHED')
            ->andReturn(m::mock())
            ->shouldReceive('getRefdataReference')
            ->with('CONDITION')
            ->andReturn(m::mock());

        $this->assertEquals(['id' => 111, 'conditionUndertakings' => ['foo', 'bar']], $this->sut->handleQuery($query));
    }
}
