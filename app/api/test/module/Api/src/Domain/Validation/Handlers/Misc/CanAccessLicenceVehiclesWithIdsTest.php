<?php

/**
 * Can Access Licence Vehicles With Ids Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\Validation\Handlers\AbstractHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanAccessLicenceVehiclesWithIds;

/**
 * Can Access Licence Vehicles With Ids Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CanAccessLicenceVehiclesWithIdsTest extends AbstractHandlerTestCase
{
    /**
     * @var CanAccessLicenceVehiclesWithIds
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new CanAccessLicenceVehiclesWithIds();

        parent::setUp();
    }

    /**
     * @dataProvider provider
     */
    public function testIsValid($canAccess1, $canAccess2, $expected)
    {
        /** @var CommandInterface $dto */
        $dto = m::mock(CommandInterface::class);
        $dto->shouldReceive('getIds')->andReturn([111, 222]);

        $this->setIsValid('canAccessLicenceVehicle', [111], $canAccess1);
        $this->setIsValid('canAccessLicenceVehicle', [222], $canAccess2);

        $this->assertSame($expected, $this->sut->isValid($dto));
    }

    public function provider()
    {
        return [
            [true, true, true],
            [false, false, false],
            [false, true, false],
            [true, false, false],
        ];
    }
}
