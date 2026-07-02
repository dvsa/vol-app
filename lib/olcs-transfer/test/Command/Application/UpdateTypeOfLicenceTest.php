<?php

namespace Dvsa\OlcsTest\Transfer\Command\Application;

use Dvsa\Olcs\Transfer\Command\Application\UpdateTypeOfLicence;
use PHPUnit\Framework\TestCase;

/**
 * Update Type Of Licence test
 */
class UpdateTypeOfLicenceTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $id = 111;
        $version = 2;
        $operatorType = 'lcat_gv';
        $licenceType = 'ltyp_sn';
        $vehicleType = 'app_veh_type_mixed';
        $lgvDeclarationConfirmation = '1';
        $niFlag = 'N';
        $confirm = false;

        $data = [
            'id' => $id,
            'version' => $version,
            'operatorType' => $operatorType,
            'licenceType' => $licenceType,
            'vehicleType' => $vehicleType,
            'lgvDeclarationConfirmation' => $lgvDeclarationConfirmation,
            'niFlag' => $niFlag,
            'confirm' => $confirm,
        ];

        $command = UpdateTypeOfLicence::create($data);

        $this->assertEquals($id, $command->getId());
        $this->assertEquals($version, $command->getVersion());
        $this->assertEquals($operatorType, $command->getOperatorType());
        $this->assertEquals($licenceType, $command->getLicenceType());
        $this->assertEquals($vehicleType, $command->getVehicleType());
        $this->assertEquals($lgvDeclarationConfirmation, $command->getLgvDeclarationConfirmation());
        $this->assertEquals($niFlag, $command->getNiFlag());
        $this->assertEquals($confirm, $command->getConfirm());
    }
}
