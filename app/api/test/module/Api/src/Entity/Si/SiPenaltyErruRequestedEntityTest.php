<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Si;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\Si\SiPenalty;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Si\SiPenaltyErruRequested as Entity;
use Dvsa\Olcs\Api\Entity\Si\SiPenaltyRequestedType;
use Dvsa\Olcs\Api\Entity\Si\SeriousInfringement;
use Mockery as m;

/**
 * SiPenaltyErruRequested Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class SiPenaltyErruRequestedEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    /**
     * Tests entity creation
     */
    public function testCreate(): void
    {
        $si = m::mock(SeriousInfringement::class);
        $siPenaltyRequestedType = m::mock(SiPenaltyRequestedType::class);
        $duration = 30;
        $penaltyRequestedIdentifier = 888;

        $entity = new Entity($si, $siPenaltyRequestedType, $duration, $penaltyRequestedIdentifier);

        $this->assertEquals($si, $entity->getSeriousInfringement());
        $this->assertEquals($siPenaltyRequestedType, $entity->getSiPenaltyRequestedType());
        $this->assertEquals($duration, $entity->getDuration());
        $this->assertEquals($penaltyRequestedIdentifier, $entity->getPenaltyRequestedIdentifier());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHasRequestedPenalties')]
    public function testHasAppliedPenalty(ArrayCollection $appliedPenalties, bool $expectedResult): void
    {
        /** @var Entity|m\LegacyMockInterface $entity */
        $entity = m::mock(Entity::class)->makePartial();
        $entity->setAppliedPenalties($appliedPenalties);
        $this->assertEquals($expectedResult, $entity->hasAppliedPenalty());
    }

    public static function dpHasRequestedPenalties(): array
    {
        return [
            [new ArrayCollection(), false],
            [new ArrayCollection([m::mock(SiPenalty::class)]), true]
        ];
    }
}
