<?php

declare(strict_types=1);

/**
 * ConditionsUndertakings Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Bookmark;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\Bookmark\ConditionsUndertakings;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Licence as Repo;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\ConditionsUndertakings as Qry;
use Dvsa\Olcs\Api\Entity\Licence\Licence as Entity;
use PHPUnit\Framework\Attributes\Group;

/**
 * ConditionsUndertakings Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class ConditionsUndertakingsTest extends QueryHandlerTestCase
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

        $attachedTo = m::mock(RefData::class);
        $conditionType = m::mock(RefData::class);

        $cu1 = $this->condition(0, 0, $attachedTo, $conditionType, $bundle, 'foo');
        $cu2 = $this->condition(0, 0, $attachedTo, $conditionType, $bundle, 'bar');

        /** @var Entity $application */
        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('serialize')
            ->with([])
            ->andReturn(['id' => 111]);

        $entity->shouldReceive('getConditionUndertakings')
            ->once()
            ->andReturn(new ArrayCollection([$cu1, $cu2]));

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($entity)
            ->shouldReceive('getRefdataReference')
            ->with('ATTACHED')
            ->andReturn($attachedTo)
            ->shouldReceive('getRefdataReference')
            ->with('CONDITION')
            ->andReturn($conditionType);

        $this->assertEquals(['id' => 111, 'conditionUndertakings' => ['foo', 'bar']], $this->sut->handleQuery($query));
    }

    public function testHandleQueryKeepsOnlyConditionsOfTheRequestedTypeHoldingTheDefaultsWhenTheConditionListIsLoaded(): void
    {
        $attachedTo = m::mock(RefData::class);
        $conditionType = m::mock(RefData::class);
        $otherType = m::mock(RefData::class);
        $otherAttachedTo = m::mock(RefData::class);
        $bundle = ['attachedTo', 'conditionType'];

        $kept = $this->condition(0, 0, $attachedTo, $conditionType, $bundle, 'kept');
        $droppedType = $this->condition(0, 0, $attachedTo, $otherType, $bundle, 'droppedType');
        $droppedAttached = $this->condition(0, 0, $otherAttachedTo, $conditionType, $bundle, 'droppedAttached');

        $this->assertSame(
            ['kept'],
            $this->handle([$droppedType, $kept, $droppedAttached], $attachedTo, $conditionType)['conditionUndertakings']
        );
    }

    #[Group('pins-current-behaviour')]
    public function testHandleQueryDropsAConditionLoadedWithNoAndNoWhenTheConditionListIsLoaded(): void
    {
        $attachedTo = m::mock(RefData::class);
        $conditionType = m::mock(RefData::class);
        $bundle = ['attachedTo', 'conditionType'];

        $loaded = $this->condition('N', 'N', $attachedTo, $conditionType, $bundle, 'loaded');
        $draft = $this->condition('Y', 0, $attachedTo, $conditionType, $bundle, 'draft');
        $fulfilled = $this->condition(0, 'Y', $attachedTo, $conditionType, $bundle, 'fulfilled');

        $this->assertSame(
            [],
            $this->handle([$loaded, $draft, $fulfilled], $attachedTo, $conditionType)['conditionUndertakings']
        );
    }

    private function handle(array $conditions, RefData $attachedTo, RefData $conditionType): array
    {
        $query = Qry::create(['id' => 111, 'attachedTo' => 'ATTACHED', 'conditionType' => 'CONDITION']);

        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('serialize')->with([])->andReturn(['id' => 111]);
        $entity->shouldReceive('getConditionUndertakings')->andReturn(new ArrayCollection($conditions));

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($entity)
            ->shouldReceive('getRefdataReference')
            ->with('ATTACHED')
            ->andReturn($attachedTo)
            ->shouldReceive('getRefdataReference')
            ->with('CONDITION')
            ->andReturn($conditionType);

        return $this->sut->handleQuery($query);
    }

    private function condition(mixed $isDraft, mixed $isFulfilled, RefData $attachedTo, RefData $conditionType, array $bundle, string $serialized): m\MockInterface
    {
        $condition = m::mock(ConditionUndertaking::class)->makePartial();
        $condition->setIsDraft($isDraft);
        $condition->setIsFulfilled($isFulfilled);
        $condition->setAttachedTo($attachedTo);
        $condition->setConditionType($conditionType);
        $condition->shouldReceive('serialize')->with($bundle)->andReturn($serialized);

        return $condition;
    }
}
