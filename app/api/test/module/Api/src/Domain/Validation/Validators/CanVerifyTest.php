<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanVerify as Sut;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;

class CanVerifyTest extends AbstractHandlerTestCase
{
    /**
     * @var Sut
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new Sut();

        parent::setUp();
    }

    public function testIsValidForOperatorUser(): void
    {
        $dto = m::mock(CommandInterface::class);
        $this->setIsGranted(Permission::OPERATOR_ADMIN, false);
        $this->setIsGranted(Permission::OPERATOR_TC, false);
        $this->setIsGranted(Permission::OPERATOR_USER, true);

        $this->assertTrue($this->sut->isValid($dto));
    }

    public function testIsValidForOperatorAdmin(): void
    {
        $dto = m::mock(CommandInterface::class);
        $this->setIsGranted(Permission::OPERATOR_ADMIN, true);

        $this->assertTrue($this->sut->isValid($dto));
    }

    public function testIsValidForOperatorTransportConsultant(): void
    {
        $dto = m::mock(CommandInterface::class);
        $this->setIsGranted(Permission::OPERATOR_ADMIN, false);
        $this->setIsGranted(Permission::OPERATOR_TC, true);

        $this->assertTrue($this->sut->isValid($dto));
    }

    /**
     * testIsValidForTransportManagerContextOnly
     */
    public function testIsValidForTransportManagerContextOnly(): void
    {
        $dto = m::mock(ProcessSignatureResponse::class);

        $this->setIsGranted(Permission::OPERATOR_ADMIN, false);
        $this->setIsGranted(Permission::OPERATOR_TC, false);
        $this->setIsGranted(Permission::OPERATOR_USER, false);
        $this->setIsGranted(Permission::TRANSPORT_MANAGER, true);
        $dto->expects('getTransportManagerApplication')->withNoArgs()->andReturn(1);

        $this->assertTrue($this->sut->isValid($dto));
    }

    public function testFailsValidationIfNotProcessSignatureAndTMA(): void
    {
        $dto = m::mock(GetAuthRequest::class);

        $this->setIsGranted(Permission::OPERATOR_ADMIN, false);
        $this->setIsGranted(Permission::OPERATOR_TC, false);
        $this->setIsGranted(Permission::OPERATOR_USER, false);
        $this->setIsGranted(Permission::TRANSPORT_MANAGER, true);

        $this->assertFalse($this->sut->isValid($dto));
    }
}
