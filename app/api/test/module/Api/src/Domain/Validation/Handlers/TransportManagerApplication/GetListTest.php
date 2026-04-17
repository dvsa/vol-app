<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\TransportManagerApplication;

use Dvsa\Olcs\Api\Domain\Validation\Validators\CanAccessApplication;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\TransportManagerApplication\GetList;

class GetListTest extends AbstractHandlerTestCase
{
    /**
     * @var Delete
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new GetList();

        parent::setUp();
    }

    public function testIsValidApplication(): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getApplication')->andReturn(24);

        $mockApplicationValidator = m::mock(CanAccessApplication::class);
        $this->validatorManager->setService('canAccessApplication', $mockApplicationValidator);

        $mockApplicationValidator->shouldReceive('isValid')->with(24)->andReturn(true);

        $this->assertSame(true, $this->sut->isValid($dto));
    }

    public function testIsValidApplicationFailed(): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getApplication')->andReturn(24);

        $mockApplicationValidator = m::mock(CanAccessApplication::class);
        $this->validatorManager->setService('canAccessApplication', $mockApplicationValidator);

        $mockApplicationValidator->shouldReceive('isValid')->with(24)->andReturn(false);

        $this->assertSame(false, $this->sut->isValid($dto));
    }

    public function testIsValidUser(): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getApplication')->andReturn(null);
        $dto->shouldReceive('getUser')->andReturn(11);

        $user = $this->mockUser();
        $user->shouldReceive('getId')->with()->once()->andReturn(11);

        $this->assertSame(true, $this->sut->isValid($dto));
    }

    public function testIsValidUserFalse(): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getApplication')->andReturn(null);
        $dto->shouldReceive('getUser')->andReturn(11);

        $user = $this->mockUser();
        $user->shouldReceive('getId')->with()->once()->andReturn(12);

        $this->assertSame(false, $this->sut->isValid($dto));
    }

    public function testIsValidInternalUser(): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getApplication')->andReturn(null);
        $dto->shouldReceive('getUser')->andReturn(null);

        $this->auth->shouldReceive('isGranted')->andReturn(true);

        $this->assertSame(true, $this->sut->isValid($dto));
    }

    public function testIsValidInternalUserFalse(): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getApplication')->andReturn(null);
        $dto->shouldReceive('getUser')->andReturn(null);

        $this->auth->shouldReceive('isGranted')->andReturn(false);

        $this->assertSame(false, $this->sut->isValid($dto));
    }
}
