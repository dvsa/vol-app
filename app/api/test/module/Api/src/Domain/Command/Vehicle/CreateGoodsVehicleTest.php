<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Command\Vehicle;

use Dvsa\Olcs\Api\Domain\Command\Vehicle\CreateGoodsVehicle;

/**
 * Create Goods Vehicle Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CreateGoodsVehicleTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $data = [
            'licence' => 99,
            'vrm' => 'VRM1',
            'platedWeight' => 1000,
            'specifiedDate' => '01/01/2017',
            'receivedDate' => '01/01/2018',
            'confirm' => 'N',
            'identifyDuplicates' => true,
            'applicationId' => 1
        ];

        $command = CreateGoodsVehicle::create($data);

        $this->assertEquals(99, $command->getLicence());
        $this->assertEquals('VRM1', $command->getVrm());
        $this->assertEquals(1000, $command->getPlatedWeight());
        $this->assertEquals('01/01/2017', $command->getSpecifiedDate());
        $this->assertEquals('01/01/2018', $command->getReceivedDate());
        $this->assertEquals('N', $command->getConfirm());
        $this->assertEquals(1, $command->getApplicationId());
        $this->assertTrue($command->getIdentifyDuplicates());

        $this->assertEquals(
            $data,
            $command->getArrayCopy()
        );
    }
}
