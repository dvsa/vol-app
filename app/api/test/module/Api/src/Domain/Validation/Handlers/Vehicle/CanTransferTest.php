<?php

declare(strict_types=1);

/**
 * Can Transfer Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Vehicle;

use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Vehicle\CanTransfer;

/**
 * Can Transfer Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CanTransferTest extends AbstractHandlerTestCase
{
    /**
     * @var CanTransfer
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanTransfer();

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testIsValid(mixed $canAccessSource, mixed $canAccessTarget, mixed $canAccessLv1, mixed $canAccessLv2, mixed $expected): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getId')->andReturn(111);
        $dto->shouldReceive('getTarget')->andReturn(222);
        $dto->shouldReceive('getLicenceVehicles')->andReturn([11, 22]);

        $this->setIsValid('canAccessLicence', [111], $canAccessSource);
        $this->setIsValid('canAccessLicence', [222], $canAccessTarget);
        $this->setIsValid('canAccessLicenceVehicle', [11], $canAccessLv1);
        $this->setIsValid('canAccessLicenceVehicle', [22], $canAccessLv2);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }

    public static function provider(): array
    {
        return [
            [true, true, true, true, true],
            [true, true, true, false, false],
            [true, true, false, true, false],
            [true, false, true, true, false],
            [false, true, true, true, false],
        ];
    }
}
