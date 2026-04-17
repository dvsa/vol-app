<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Si;

use Dvsa\Olcs\Api\Entity\Si\SiPenaltyErruRequested;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Si\SiPenalty as Entity;
use Dvsa\Olcs\Api\Entity\Si\SeriousInfringement;
use Dvsa\Olcs\Api\Entity\Si\SiPenaltyType;
use Mockery as m;

/**
 * SiPenalty Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class SiPenaltyEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    /**
     * Tests create and update
     */
    public function testCreateAndUpdate(): void
    {
        $si = m::mock(SeriousInfringement::class);
        $penaltyType = m::mock(SiPenaltyType::class);
        $erruRequested = m::mock(SiPenaltyErruRequested::class);
        $startDate = new \DateTime('2015-12-25');
        $endDate = new \DateTime('2015-12-26');
        $imposed = 'Y';
        $reasonNotImposed = 'reason not imposed';

        $entity = new Entity($si, $penaltyType, $erruRequested, $imposed, $startDate, $endDate, $reasonNotImposed);

        $this->assertEquals($si, $entity->getSeriousInfringement());
        $this->assertEquals($penaltyType, $entity->getSiPenaltyType());
        $this->assertEquals($startDate, $entity->getStartDate());
        $this->assertEquals($endDate, $entity->getEndDate());
        $this->assertEquals($imposed, $entity->getImposed());
        $this->assertEquals($reasonNotImposed, $entity->getReasonNotImposed());
        $this->assertEquals($erruRequested, $entity->getSiPenaltyErruRequested());
    }
}
