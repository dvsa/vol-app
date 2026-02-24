<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Cases;

use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking as Entity;
use Mockery as m;
use Dvsa\Olcs\Api\Entity\System\RefData as RefDataEntity;

/**
 * ConditionUndertaking Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class ConditionUndertakingEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    /**
     * Test constructor
     */
    public function testConstructor(): void
    {
        $mockConditionType = m::mock(RefDataEntity::class);

        $entity = new Entity($mockConditionType, true, true);

        $this->assertEquals($mockConditionType, $entity->getConditionType());
        $this->assertTrue($entity->getIsFulfilled());
        $this->assertTrue($entity->getIsDraft());
    }
}
