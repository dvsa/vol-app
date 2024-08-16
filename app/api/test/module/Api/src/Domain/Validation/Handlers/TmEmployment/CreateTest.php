<?php

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\TmEmployment;

use Dvsa\Olcs\Api\Domain\Validation\Validators\CanAccessTransportManager;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\TmEmployment\Create;

class CreateTest extends AbstractHandlerTestCase
{
    /**
     * @var Create
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new Create();

        parent::setUp();
    }

    public function testIsValidInternalUser()
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);

        $this->auth->shouldReceive('isGranted')
            ->with(Permission::INTERNAL_USER, null)->once()
            ->andReturn(true);

        $this->assertSame(true, $this->sut->isValid($dto));
    }

    /**
     * @dataProvider provider
     */
    public function testIsValid($canAccess, $expected)
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getTmaId')->andReturn(1);

        $this->auth->shouldReceive('isGranted')
            ->with(Permission::INTERNAL_USER, null)->once()
            ->andReturn(false);

        $mockValidator = m::mock(CanAccessTransportManager::class);
        $this->validatorManager->setService('canAccessTransportManagerApplication', $mockValidator);

        $mockValidator->shouldReceive('isValid')->with(1)->andReturn($canAccess);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }

    /**
     * @dataProvider provider
     */
    public function testIsValidTm($canAccess, $expected)
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getTmaId')->andReturn(null);
        $dto->shouldReceive('getTransportManager')->andReturn(1);

        $this->auth->shouldReceive('isGranted')
            ->with(Permission::INTERNAL_USER, null)->once()
            ->andReturn(false);

        $mockValidator = m::mock(CanAccessTransportManager::class);
        $this->validatorManager->setService('canAccessTransportManager', $mockValidator);

        $mockValidator->shouldReceive('isValid')->with(1)->andReturn($canAccess);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }

    public function testNotValid()
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getTmaId')->andReturn(null);
        $dto->shouldReceive('getTransportManager')->andReturn(null);

        $this->auth->shouldReceive('isGranted')
            ->with(Permission::INTERNAL_USER, null)->once()
            ->andReturn(false);

        $this->assertSame(false, $this->sut->isValid($dto));
    }

    /**
     * @return array
     */
    public function provider()
    {
        return [
            [true, true],
            [false, false],
        ];
    }
}
