<?php

namespace Dvsa\OlcsTest\Transfer\Command\LicenceVehicle;

use Dvsa\Olcs\Transfer\Command\LicenceVehicle\UpdateUnlicensedOperatorLicenceVehicle;

/**
 * UpdateUnlicensedOperatorLicenceVehicle test
 */
class UpdateUnlicensedOperatorLicenceVehicleTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = [
            'id' => 69,
            'version' => 1,
            'vrm' => 'ABC123',
            'platedWeight' => 895,
        ];

        $command = UpdateUnlicensedOperatorLicenceVehicle::create($data);

        $this->assertEquals(69, $command->getId());
        $this->assertEquals(1, $command->getVersion());
        $this->assertEquals('ABC123', $command->getVrm());
        $this->assertEquals(895, $command->getPlatedWeight());
    }
}
