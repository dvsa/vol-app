<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Permits;

use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as IrhpApplicationEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplicationReadAudit as Entity;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Mockery as m;

/**
 * IrhpApplicationReadAudit Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class IrhpApplicationReadAuditEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testConstrunctor(): void
    {
        /** @var UserEntity $mockUser */
        $mockUser = m::mock(UserEntity::class);

        /** @var IrhpApplicationEntity $mockIrhpAppEntity */
        $mockIrhpAppEntity = m::mock(IrhpApplicationEntity::class);

        $sut = new Entity($mockUser, $mockIrhpAppEntity);

        static::assertSame($mockUser, $sut->getUser());
        static::assertSame($mockIrhpAppEntity, $sut->getIrhpApplication());
    }
}
