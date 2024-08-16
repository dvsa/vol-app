<?php

namespace Dvsa\OlcsTest\Api\Entity\Si;

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
    public function testCreate()
    {
        $si = m::mock(SeriousInfringement::class);
        $siPenaltyRequestedType = m::mock(SiPenaltyRequestedType::class);
        $duration = 30;

        $entity = new Entity($si, $siPenaltyRequestedType, $duration);

        $this->assertEquals($si, $entity->getSeriousInfringement());
        $this->assertEquals($siPenaltyRequestedType, $entity->getSiPenaltyRequestedType());
        $this->assertEquals($duration, $entity->getDuration());
    }
}
