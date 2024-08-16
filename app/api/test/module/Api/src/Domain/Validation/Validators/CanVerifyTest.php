<?php

namespace Dvsa\OlcsTest\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanVerify as Sut;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\GdsVerify\ProcessSignatureResponse;
use Dvsa\Olcs\Transfer\Query\GdsVerify\GetAuthRequest;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;

/**
 * Can Manage User Test
 */
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


    /**
     * testIsValidForOperatorUser
     */
    public function testIsValidForOperatorUser()
    {

        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $this->setIsGranted(Permission::OPERATOR_ADMIN, false);
        $this->setIsGranted(Permission::OPERATOR_USER, true);

        $dto->shouldReceive('getTransportManagerApplication')->andReturn(0);
        $this->assertTrue($this->sut->isValid($dto));
    }

    public function testIsValidForOperator()
    {

        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $this->setIsGranted(Permission::OPERATOR_ADMIN, true);

        $dto->shouldReceive('getTransportManagerApplication')->andReturn(0);
        $this->assertTrue($this->sut->isValid($dto));
    }

    /**
     * testIsValidForTransportManagerContextOnly
     */
    public function testIsValidForTransportManagerContextOnly()
    {

        /** @var CommandInterface $dto */
        $dto = m::mock(ProcessSignatureResponse::class);

        $this->setIsGranted(Permission::OPERATOR_ADMIN, false);
        $this->setIsGranted(Permission::OPERATOR_USER, false);
        $this->setIsGranted(Permission::TRANSPORT_MANAGER, true);
        $dto->shouldReceive('getTransportManagerApplication')->andReturn(1);

        $this->assertTrue($this->sut->isValid($dto));
    }

    public function testFailsValidationIfNotProcessSignatureAndTMA()
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(GetAuthRequest::class);

        $this->setIsGranted(Permission::OPERATOR_ADMIN, false);
        $this->setIsGranted(Permission::OPERATOR_USER, false);
        $this->setIsGranted(Permission::TRANSPORT_MANAGER, true);

        $this->assertFalse($this->sut->isValid($dto));
    }
}
