<?php

declare(strict_types=1);

/**
 * Is Internal User Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsInternalUser;

/**
 * Is Internal User Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class IsInternalUserTest extends AbstractHandlerTestCase
{
    /**
     * @var IsInternalUser
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new IsInternalUser();

        parent::setUp();
    }

    public function testIsValidInternal(): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);

        $this->setIsGranted(Permission::INTERNAL_USER, true);

        $this->assertTrue($this->sut->isValid($dto));
    }

    public function testIsValidInternalFail(): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);

        $this->setIsGranted(Permission::INTERNAL_USER, false);

        $this->assertFalse($this->sut->isValid($dto));
    }
}
