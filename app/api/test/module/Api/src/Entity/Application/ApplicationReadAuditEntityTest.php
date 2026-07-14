<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Application;

use Dvsa\Olcs\Api\Entity;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * ApplicationReadAudit Entity Unit Tests
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Entity\Application\ApplicationReadAudit::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Entity\Application\AbstractApplicationReadAudit::class)]
final class ApplicationReadAuditEntityTest extends EntityTester
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

        $this->assertSame($mockUser, $sut->getUser());
        $this->assertSame($mockApp, $sut->getApplication());
    }
}
