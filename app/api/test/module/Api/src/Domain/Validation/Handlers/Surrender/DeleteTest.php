<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Surrender;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\Surrender\Delete;
use Dvsa\Olcs\Api\Domain\Validation\Validators\CanAccessLicence;
use Dvsa\Olcs\Api\Domain\Validation\Validators\CanDeleteSurrender;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;

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
    public function testIsValid(mixed $canAccess, mixed $canDelete, mixed $expected): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getId')->andReturn(7);

        $canAccessLicence = m::mock(CanAccessLicence::class);
        $this->validatorManager->setService('canAccessLicence', $canAccessLicence);
        $canAccessLicence->shouldReceive('isValid')->andReturn($canAccess);

        $canDeleteSurrender = m::mock(CanDeleteSurrender::class);
        $this->validatorManager->setService('canDeleteSurrender', $canDeleteSurrender);
        $canDeleteSurrender->shouldReceive('isValid')->andReturn($canDelete);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }

    public static function provider(): array
    {
        return [
            [
                'canAccess' => true,
                'canDelete' => true,
                'expected' => true
            ],
            [
                'canAccess' => false,
                'canDelete' =>  true,
                'expected' => false
            ],
            [
                'canAccess' => true,
                'canDelete' => false,
                'expected' => false
            ]
        ];
    }
}
