<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsSystemUserOrSystemAdmin;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;

final class IsSystemUserOrSystemAdminTest extends AbstractHandlerTestCase
{
    /**
     * @var IsSystemUserOrSystemAdmin
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new IsSystemUserOrSystemAdmin();

        parent::setUp();
    }

    public function testIsValidForSystemUser(): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);

        $mockUser = $this->mockUser();
        $mockUser->shouldReceive('isSystemUser')
            ->once()
            ->andReturn(true);

        $this->assertTrue($this->sut->isValid($dto));
    }

    public function testIsValidForSystemAdmin(): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);

        $mockUser = $this->mockUser();
        $mockUser->shouldReceive('isSystemUser')
            ->once()
            ->andReturn(false);

        $this->setIsGranted(Permission::SYSTEM_ADMIN, true);

        $this->assertTrue($this->sut->isValid($dto));
    }

    public function testIsNotValidForOrdinaryUser(): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);

        $mockUser = $this->mockUser();
        $mockUser->shouldReceive('isSystemUser')
            ->once()
            ->andReturn(false);

        $this->setIsGranted(Permission::SYSTEM_ADMIN, false);

        $this->assertFalse($this->sut->isValid($dto));
    }
}
