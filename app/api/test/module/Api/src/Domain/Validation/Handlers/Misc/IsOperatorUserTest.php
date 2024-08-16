<?php

/**
 * Is Operator Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsOperatorUser;

/**
 * Is Operator User Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class IsOperatorUserTest extends AbstractHandlerTestCase
{
    /**
     * @var isOperatorUser
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new IsOperatorUser();

        parent::setUp();
    }

    public function testIsValidOperatorAdmin()
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);

        $this->setIsGranted(Permission::OPERATOR_ADMIN, true);

        $this->assertTrue($this->sut->isValid($dto));
    }

    public function testIsValidOperator()
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);

        $this->setIsGranted(Permission::OPERATOR_ADMIN, false);
        $this->setIsGranted(Permission::OPERATOR_USER, true);

        $this->assertTrue($this->sut->isValid($dto));
    }

    public function testIsValidOperatorFail()
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);

        $this->setIsGranted(Permission::OPERATOR_ADMIN, false);
        $this->setIsGranted(Permission::OPERATOR_USER, false);

        $this->assertFalse($this->sut->isValid($dto));
    }
}
