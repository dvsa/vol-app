<?php

declare(strict_types=1);

/**
 * Is Local Authority Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsLocalAuthorityUser;

/**
 * Is Local Authority User Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class IsLocalAuthorityUserTest extends AbstractHandlerTestCase
{
    /**
     * @var isLocal AuthorityUser
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new IsLocalAuthorityUser();

        parent::setUp();
    }

    public function testIsValidLocalAuthorityAdmin(): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);

        $this->setIsGranted(Permission::LOCAL_AUTHORITY_USER, false);
        $this->setIsGranted(Permission::LOCAL_AUTHORITY_ADMIN, true);

        $this->assertTrue($this->sut->isValid($dto));
    }

    public function testIsValidLocalAuthority(): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);

        $this->setIsGranted(Permission::LOCAL_AUTHORITY_USER, true);

        $this->assertTrue($this->sut->isValid($dto));
    }

    public function testIsValidLocalAuthorityFail(): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);

        $this->setIsGranted(Permission::LOCAL_AUTHORITY_ADMIN, false);
        $this->setIsGranted(Permission::LOCAL_AUTHORITY_USER, false);

        $this->assertFalse($this->sut->isValid($dto));
    }
}
