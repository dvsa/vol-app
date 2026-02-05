<?php

declare(strict_types=1);

/**
 * Is External User Test
 */

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsExternalUser;

/**
 * Is External User Test
 */
class IsExternalUserTest extends AbstractHandlerTestCase
{
    /**
     * @var IsExternalUser
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new IsExternalUser();

        parent::setUp();
    }

    public function testIsValidExternal(): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);

        $this->setIsGranted(Permission::SELFSERVE_USER, true);

        $this->assertTrue($this->sut->isValid($dto));
    }

    public function testIsValidExternalFail(): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);

        $this->setIsGranted(Permission::SELFSERVE_USER, false);

        $this->assertFalse($this->sut->isValid($dto));
    }
}
