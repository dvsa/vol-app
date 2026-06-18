<?php

namespace Dvsa\OlcsTest\Transfer\Command\System;

use Dvsa\Olcs\Transfer\Command\System\UpdateFinancialStandingRate as Sut;

/**
 * Update Financial Standing Rate test
 */
class UpdateFinancialStandingRateTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = [
            'id' => '69',
            'version' => '1',
            'goodsOrPsv' => 'lcat_gv',
            'licenceType' => 'ltyp_sn',
            'vehicleType' => 'fin_sta_veh_typ_hgv',
            'firstVehicleRate' => '1234.56',
            'additionalVehicleRate' => '345.67',
            'effectiveFrom' => '2015-06-18',
        ];

        $command = Sut::create($data);

        $this->assertEquals('69', $command->getId());
        $this->assertEquals('1', $command->getVersion());
        $this->assertEquals('lcat_gv', $command->getGoodsOrPsv());
        $this->assertEquals('ltyp_sn', $command->getLicenceType());
        $this->assertEquals('fin_sta_veh_typ_hgv', $command->getVehicleType());
        $this->assertEquals('1234.56', $command->getFirstVehicleRate());
        $this->assertEquals('345.67', $command->getAdditionalVehicleRate());
        $this->assertEquals('2015-06-18', $command->getEffectiveFrom());
    }
}
