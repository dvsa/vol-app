<?php

declare(strict_types=1);

/**
 * Modify List Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Vehicle\Application;

use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Vehicle\Application\ModifyList;

/**
 * Modify List Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
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

    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testIsValid(mixed $canAccessApplication, mixed $canAccess1, mixed $canAccess2, mixed $expected): void
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getIds')->andReturn([111, 222]);
        $dto->shouldReceive('getApplication')->andReturn(123);

        $this->setIsValid('canAccessApplication', [123], $canAccessApplication);
        $this->setIsValid('canAccessLicenceVehicle', [111], $canAccess1);
        $this->setIsValid('canAccessLicenceVehicle', [222], $canAccess2);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }

    public static function provider(): array
    {
        return [
            [true, true, true, true],
            [false, true, true, false],
            [true, false, false, false],
            [true, false, true, false],
            [true, true, false, false],
        ];
    }
}
