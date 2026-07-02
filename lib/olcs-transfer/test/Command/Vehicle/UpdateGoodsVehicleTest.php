<?php

namespace Dvsa\OlcsTest\Transfer\Command\Vehicle;

use Dvsa\Olcs\Transfer\Command\Vehicle\UpdateGoodsVehicle;

/**
 * Update Goods Vehicle test
 */
class UpdateGoodsVehicleTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = [
            'id' => 111,
            'version' => 2,
            'platedWeight' => 1000,
            'receivedDate' => '01/01/2017',
            'specifiedDate' => '02/01/2017',
            'seedDate' => '03/01/2017',
            'sentDate' => '04/01/2017',
            'removalDate' => '05/01/2017',
        ];

        $command = UpdateGoodsVehicle::create($data);

        $this->assertEquals(111, $command->getId());
        $this->assertEquals(2, $command->getVersion());
        $this->assertEquals(1000, $command->getPlatedWeight());
        $this->assertEquals('01/01/2017', $command->getReceivedDate());
        $this->assertEquals('02/01/2017', $command->getSpecifiedDate());
        $this->assertEquals('03/01/2017', $command->getSeedDate());
        $this->assertEquals('04/01/2017', $command->getSentDate());
        $this->assertEquals('05/01/2017', $command->getRemovalDate());
    }
}
