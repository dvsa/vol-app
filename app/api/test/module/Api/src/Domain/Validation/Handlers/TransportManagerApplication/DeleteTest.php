<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\TransportManagerApplication;

use Dvsa\Olcs\Api\Domain\Validation\Validators\CanAccessTransportManagerApplication;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\TransportManagerApplication\Delete;
use Dvsa\Olcs\Api\Rbac\IdentityProviderInterface;

class DeleteTest extends AbstractHandlerTestCase
{
    /**
     * @var Delete
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new Delete();

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testIsValid(mixed $canAccess, mixed $expected, mixed $userId): void
    {
        $mockUser = $this->mockUser();
        $mockUser->shouldReceive('getId')
            ->andReturn($userId)
            ->once()
            ->getMock();

        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getIds')->andReturn([19, 11, 2015]);

        $mockValidator = m::mock(CanAccessTransportManagerApplication::class);
        $this->validatorManager->setService('canAccessTransportManagerApplication', $mockValidator);

        $mockValidator->shouldReceive('isValid')->with(19)->andReturn($canAccess);
        $mockValidator->shouldReceive('isValid')->with(11)->andReturn($canAccess);
        $mockValidator->shouldReceive('isValid')->with(2015)->andReturn($canAccess);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }

    public static function provider(): array
    {
        return [
            [true, true, 10],
            [false, false, 10],
            [false, true, IdentityProviderInterface::SYSTEM_USER]
        ];
    }
}
