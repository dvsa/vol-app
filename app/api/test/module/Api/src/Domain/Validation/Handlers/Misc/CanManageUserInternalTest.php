<?php

declare(strict_types=1);

/**
 * Can Manage User Internal Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanManageUserInternal;

/**
 * Can Manage User Internal Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CanManageUserInternalTest extends AbstractHandlerTestCase
{
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanManageUserInternal();

        parent::setUp();
    }

    public function testIsValid(): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);

        $this->setIsGranted(Permission::INTERNAL_USER, true);
        $this->setIsGranted(Permission::CAN_MANAGE_USER_INTERNAL, true);

        $this->assertTrue($this->sut->isValid($dto));
    }

    public function testIsNotValid(): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);

        $this->setIsGranted(Permission::INTERNAL_USER, true);
        $this->setIsGranted(Permission::CAN_MANAGE_USER_INTERNAL, false);

        $this->assertFalse($this->sut->isValid($dto));
    }
}
