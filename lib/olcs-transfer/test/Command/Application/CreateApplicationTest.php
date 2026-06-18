<?php

namespace Dvsa\OlcsTest\Transfer\Command\Application;

use Dvsa\Olcs\Transfer\Command\Application\CreateApplication;
use PHPUnit\Framework\TestCase;

/**
 * Create Application test
 */
class CreateApplicationTest extends TestCase
{
    public function testStructure()
    {
        $operatorType = 'lcat_gv';
        $licenceType = 'ltyp_si';
        $vehicleType = 'app_veh_type_lgv';
        $lgvDeclarationConfirmation = '1';
        $niFlag = 'N';
        $receivedDate = '2021-04-01';
        $trafficArea = 'C';
        $organisation = '123';
        $appliedVia = 'applied_via_post';

        $data = [
            'operatorType' => $operatorType,
            'licenceType' => $licenceType,
            'vehicleType' => $vehicleType,
            'lgvDeclarationConfirmation' => $lgvDeclarationConfirmation,
            'niFlag' => $niFlag,
            'receivedDate' => $receivedDate,
            'trafficArea' => $trafficArea,
            'organisation' => $organisation,
            'appliedVia' => $appliedVia,
        ];

        $command = CreateApplication::create($data);

        $this->assertEquals($operatorType, $command->getOperatorType());
        $this->assertEquals($licenceType, $command->getLicenceType());
        $this->assertEquals($vehicleType, $command->getVehicleType());
        $this->assertEquals($lgvDeclarationConfirmation, $command->getLgvDeclarationConfirmation());
        $this->assertEquals($niFlag, $command->getNiFlag());
        $this->assertEquals($receivedDate, $command->getReceivedDate());
        $this->assertEquals($trafficArea, $command->getTrafficArea());
        $this->assertEquals($organisation, $command->getOrganisation());
        $this->assertEquals($appliedVia, $command->getAppliedVia());
    }
}
