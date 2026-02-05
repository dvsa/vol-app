<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\TransportManagerApplication;

use Dvsa\Olcs\Api\Domain\Validation\Validators\CanAccessApplication;
use Dvsa\Olcs\Api\Domain\Validation\Validators\CanAccessUser;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\TransportManagerApplication\Create;

class CreateTest extends AbstractHandlerTestCase
{
    /**
     * @var Delete
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new Create();

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testIsValid(mixed $canAccess, mixed $expected): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getApplication')->andReturn(24);
        $dto->shouldReceive('getUser')->andReturn(11);

        $mockApplicationValidator = m::mock(CanAccessApplication::class);
        $this->validatorManager->setService('canAccessApplication', $mockApplicationValidator);

        $mockUserValidator = m::mock(CanAccessUser::class);
        $this->validatorManager->setService('canAccessUser', $mockUserValidator);

        $mockApplicationValidator->shouldReceive('isValid')->with(24)->andReturn($canAccess);
        $mockUserValidator->shouldReceive('isValid')->with(11)->andReturn($canAccess);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }

    public static function provider(): array
    {
        return [
            [true, true],
            [false, false],
        ];
    }
}
