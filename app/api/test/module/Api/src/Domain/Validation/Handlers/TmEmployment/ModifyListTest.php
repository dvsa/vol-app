<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\TmEmployment;

use Dvsa\Olcs\Api\Domain\Validation\Validators\CanAccessTmEmployment;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\TmEmployment\ModifyList;

class ModifyListTest extends AbstractHandlerTestCase
{
    /**
     * @var ModifyList
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new ModifyList();

        parent::setUp();
    }

    public function testIsValidInternalUser(): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);

        $this->auth->shouldReceive('isGranted')
            ->with(Permission::INTERNAL_USER, null)->once()
            ->andReturn(true);

        $this->assertSame(true, $this->sut->isValid($dto));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testIsValid(mixed $canAccess, mixed $expected): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getIds')->andReturn([1, 2]);

        $this->auth->shouldReceive('isGranted')
            ->with(Permission::INTERNAL_USER, null)->once()
            ->andReturn(false);

        $mockValidator = m::mock(CanAccessTmEmployment::class);
        $this->validatorManager->setService('canAccessTmEmployment', $mockValidator);

        $mockValidator->shouldReceive('isValid')->with(1)->andReturn($canAccess);
        $mockValidator->shouldReceive('isValid')->with(2)->andReturn($canAccess);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }
    /**
     * @return array
     */
    public static function provider(): array
    {
        return [
            [true, true],
            [false, false],
        ];
    }
}
