<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\IsOperatorUser;

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

    public function testIsValidOperatorAdmin(): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);

        $this->setIsGranted(Permission::OPERATOR_ADMIN, true);

        $this->assertTrue($this->sut->isValid($dto));
    }

    public function testIsValidOperatorTc(): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);

        $this->setIsGranted(Permission::OPERATOR_ADMIN, false);
        $this->setIsGranted(Permission::OPERATOR_TC, true);

        $this->assertTrue($this->sut->isValid($dto));
    }

    public function testIsValidOperator(): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);

        $this->setIsGranted(Permission::OPERATOR_ADMIN, false);
        $this->setIsGranted(Permission::OPERATOR_TC, false);
        $this->setIsGranted(Permission::OPERATOR_USER, true);

        $this->assertTrue($this->sut->isValid($dto));
    }

    public function testIsValidOperatorFail(): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);

        $this->setIsGranted(Permission::OPERATOR_ADMIN, false);
        $this->setIsGranted(Permission::OPERATOR_TC, false);
        $this->setIsGranted(Permission::OPERATOR_USER, false);

        $this->assertFalse($this->sut->isValid($dto));
    }
}
