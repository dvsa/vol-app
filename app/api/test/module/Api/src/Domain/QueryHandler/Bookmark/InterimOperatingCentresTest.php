<?php

declare(strict_types=1);

/**
 * InterimOperatingCentres Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Bookmark;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre;
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
final class InterimOperatingCentresTest extends QueryHandlerTestCase
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

        $oc1 = $this->operatingCentre('Y');
        $oc1->shouldReceive('serialize')
            ->with($bundle)
            ->andReturn('foo');
        $oc2 = $this->operatingCentre('Y');
        $oc2->shouldReceive('serialize')
            ->with($bundle)
            ->andReturn('bar');
        $notInterim = $this->operatingCentre('N');
        $notInterim->shouldReceive('serialize')->never();

        $entity->shouldReceive('getOperatingCentres')
            ->andReturn(new ArrayCollection([$oc1, $notInterim, $oc2]));

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

    private function operatingCentre(string $isInterim): m\MockInterface
    {
        $oc = m::mock(ApplicationOperatingCentre::class)->makePartial();
        $oc->setIsInterim($isInterim);

        return $oc;
    }
}
