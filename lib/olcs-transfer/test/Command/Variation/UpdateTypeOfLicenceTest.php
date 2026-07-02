<?php

namespace Dvsa\OlcsTest\Transfer\Command\Variation;

use Dvsa\Olcs\Transfer\Command\Variation\UpdateTypeOfLicence;

/**
 * Update Business Type test
 */
class UpdateTypeOfLicenceTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = [
            'id' => 111,
            'version' => 2,
            'licenceType' => 'ltyp_sn',
            'vehicleType' => 'app_veh_type_mixed',
            'lgvDeclarationConfirmation' => '1',
            'confirm' => true,
            'foo' => 'bar'
        ];

        $command = UpdateTypeOfLicence::create($data);

        $this->assertEquals(111, $command->getId());
        $this->assertEquals(2, $command->getVersion());
        $this->assertEquals('ltyp_sn', $command->getLicenceType());
        $this->assertEquals('app_veh_type_mixed', $command->getVehicleType());
        $this->assertEquals('1', $command->getLgvDeclarationConfirmation());
        $this->assertTrue($command->getConfirm());
    }
}
