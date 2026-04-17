<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Application;

use Dvsa\Olcs\Api\Entity;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * ApplicationReadAudit Entity Unit Tests
 *
 * @covers Dvsa\Olcs\Api\Entity\Application\ApplicationReadAudit
 * @covers Dvsa\Olcs\Api\Entity\Application\AbstractApplicationReadAudit
 */
class ApplicationReadAuditEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity\Application\ApplicationReadAudit::class;

    public function testConstrunctor(): void
    {
        /** @var Entity\User\User $mockUser */
        $mockUser = m::mock(Entity\User\User::class);
        /** @var Entity\Application\Application $mockApp */
        $mockApp = m::mock(Entity\Application\Application::class);

        $sut = new Entity\Application\ApplicationReadAudit($mockUser, $mockApp);

        static::assertSame($mockUser, $sut->getUser());
        static::assertSame($mockApp, $sut->getApplication());
    }
}
