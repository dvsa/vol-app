<?php

declare(strict_types=1);

/**
 * InterimConditionsUndertakings Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Bookmark;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\Bookmark\InterimConditionsUndertakings;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Application as Repo;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\InterimConditionsUndertakings as Qry;
use Dvsa\Olcs\Api\Entity\Application\Application as Entity;
use PHPUnit\Framework\Attributes\Group;

/**
 * InterimConditionsUndertakings Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class InterimConditionsUndertakingsTest extends QueryHandlerTestCase
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

    public function testHandleQueryBundleKeepsOnlyConditionsOfTheRequestedTypeHoldingIntZeroWhenTheConditionListIsLoaded(): void
    {
        $attachedTo = m::mock(RefData::class);
        $conditionType = m::mock(RefData::class);

        $kept = $this->condition(0, 0, $attachedTo, $conditionType);
        $droppedType = $this->condition(0, 0, $attachedTo, m::mock(RefData::class));
        $droppedAttached = $this->condition(0, 0, m::mock(RefData::class), $conditionType);

        $this->assertSame(
            [$kept],
            $this->filter([$droppedType, $kept, $droppedAttached], $attachedTo, $conditionType)
        );
    }

    #[Group('pins-current-behaviour')]
    public function testHandleQueryBundleDropsALoadedApprovedUnfulfilledConditionWhenTheConditionListIsLoaded(): void
    {
        $attachedTo = m::mock(RefData::class);
        $conditionType = m::mock(RefData::class);

        $loaded = $this->condition('N', 'N', $attachedTo, $conditionType);
        $draft = $this->condition('Y', 0, $attachedTo, $conditionType);
        $fulfilled = $this->condition(0, 'Y', $attachedTo, $conditionType);

        $this->assertSame([], $this->filter([$loaded, $draft, $fulfilled], $attachedTo, $conditionType));
    }

    private function filter(array $conditions, RefData $attachedTo, RefData $conditionType): array
    {
        $query = Qry::create(['id' => 111, 'attachedTo' => 'ATTACHED', 'conditionType' => 'CONDITION']);

        $matching = null;
        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('serialize')
            ->once()
            ->andReturnUsing(
                function (array $bundle) use ($conditions, &$matching) {
                    $matching = (new ArrayCollection($conditions))
                        ->matching($bundle['licence']['conditionUndertakings']['criteria'])
                        ->getValues();

                    return ['id' => 111];
                }
            );

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($entity)
            ->shouldReceive('getRefdataReference')
            ->with('ATTACHED')
            ->andReturn($attachedTo)
            ->shouldReceive('getRefdataReference')
            ->with('CONDITION')
            ->andReturn($conditionType);

        $this->sut->handleQuery($query);

        return $matching;
    }

    private function condition(mixed $isDraft, mixed $isFulfilled, RefData $attachedTo, RefData $conditionType): ConditionUndertaking
    {
        $condition = new ConditionUndertaking($conditionType, $isFulfilled, $isDraft);
        $condition->setAttachedTo($attachedTo);

        return $condition;
    }
}
