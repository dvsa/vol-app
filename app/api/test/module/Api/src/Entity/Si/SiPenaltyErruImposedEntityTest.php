<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Si;

use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Si\SiPenaltyErruImposed as Entity;
use Dvsa\Olcs\Api\Entity\Si\SiPenaltyImposedType;
use Dvsa\Olcs\Api\Entity\Si\SeriousInfringement;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Mockery as m;

/**
 * SiPenaltyErruImposed Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class SiPenaltyErruImposedEntityTest extends EntityTester
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
        $siPenaltyImposedType = m::mock(SiPenaltyImposedType::class);
        $executed = m::mock(RefData::class);
        $startDate = new \DateTime('2015-12-24');
        $endDate = new \DateTime('2015-12-25');
        $finalDecisionDate = new \DateTime('2015-12-26');
        $penaltyImposedIdentifier = 999;

        $entity = new Entity($si, $siPenaltyImposedType, $executed, $startDate, $endDate, $finalDecisionDate, $penaltyImposedIdentifier);

        $this->assertEquals($siPenaltyImposedType, $entity->getSiPenaltyImposedType());
        $this->assertEquals($executed, $entity->getExecuted());
        $this->assertEquals($startDate, $entity->getStartDate());
        $this->assertEquals($endDate, $entity->getEndDate());
        $this->assertEquals($finalDecisionDate, $entity->getFinalDecisionDate());
        $this->assertEquals($penaltyImposedIdentifier, $entity->getPenaltyImposedIdentifier());
    }
}
