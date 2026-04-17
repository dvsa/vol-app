<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsSystemAdmin;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;

/**
 * IsSystemAdminTest
 */
class IsSystemAdminTest extends AbstractHandlerTestCase
{
    /**
     * @var IsSystemAdmin
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new IsSystemAdmin();

        parent::setUp();
    }

    public function testIsValid(): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);

        $this->setIsGranted(Permission::SYSTEM_ADMIN, true);

        $this->assertTrue($this->sut->isValid($dto));
    }

    public function testIsValidFail(): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);

        $this->setIsGranted(Permission::SYSTEM_ADMIN, false);

        $this->assertFalse($this->sut->isValid($dto));
    }
}
