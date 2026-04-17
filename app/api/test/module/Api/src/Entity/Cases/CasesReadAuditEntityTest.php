<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Cases;

use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Cases\CasesReadAudit as Entity;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Mockery as m;

/**
 * CasesReadAudit Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class CasesReadAuditEntityTest extends EntityTester
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
        $mockUser = m::mock(UserEntity::class);
        $mockCase = m::mock(CasesEntity::class);

        $entity = new Entity($mockUser, $mockCase);

        $this->assertEquals($mockUser, $entity->getUser());
        $this->assertEquals($mockCase, $entity->getCase());
    }
}
