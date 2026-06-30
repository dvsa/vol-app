<?php

namespace Dvsa\OlcsTest\Transfer\Command\Variation;

use Dvsa\Olcs\Transfer\Command\Variation\UpdateInterim;

class UpdateInterimTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = [
            'requested' => 'Y',
            'reason' => 'many reasons',
            'startDate' => '2018-01-02',
            'endDate' => '2018-05-30',
            'authHgvVehicles' => 4,
            'authLgvVehicles' => 5,
            'authTrailers' => 6,
            'operatingCentres' => 1,
            'vehicles' => 2,
            'status' => 'int_sts_requested',
            'action' => 'grant',
        ];

        $command = UpdateInterim::create($data);

        $this->assertEquals('Y', $command->getRequested());
        $this->assertEquals('many reasons', $command->getReason());
        $this->assertEquals('2018-01-02', $command->getStartDate());
        $this->assertEquals('2018-05-30', $command->getEndDate());
        $this->assertEquals(4, $command->getAuthHgvVehicles());
        $this->assertEquals(5, $command->getAuthLgvVehicles());
        $this->assertEquals(6, $command->getAuthTrailers());
        $this->assertEquals(1, $command->getOperatingCentres());
        $this->assertEquals(2, $command->getVehicles());
        $this->assertEquals('int_sts_requested', $command->getStatus());
        $this->assertEquals('grant', $command->getAction());
    }
}
