<?php

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsInternalAdmin;

/**
 * IsInternalAdminTest
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class IsInternalAdminTest extends AbstractHandlerTestCase
{
    /**
     * @var IsInternalAdmin
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new IsInternalAdmin();

        parent::setUp();
    }

    public function testIsValidInternal()
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);

        $this->setIsGranted(Permission::INTERNAL_ADMIN, true);

        $this->assertTrue($this->sut->isValid($dto));
    }

    public function testIsValidInternalFail()
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);

        $this->setIsGranted(Permission::INTERNAL_ADMIN, false);

        $this->assertFalse($this->sut->isValid($dto));
    }
}
