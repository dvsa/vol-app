<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Command\TransportManagerLicence;

use Dvsa\Olcs\Transfer\Command\TransportManagerLicence\UpdateForResponsibilities;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Command\TransportManagerLicence\UpdateForResponsibilities::class)]
final class UpdateForResponsibilitiesTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = [
            'id' => 999,
            'version' => 888,
            'tmType' => 'unit_tmType',
            'isOwner' => 'unit_isOwner',
            'hoursMon' => 'unit_hoursMon',
            'hoursTue' => 'unit_hoursTue',
            'hoursWed' => 'unit_hoursWed',
            'hoursThu' => 'unit_hoursThu',
            'hoursFri' => 'unit_hoursFri',
            'hoursSat' => 'unit_hoursSat',
            'hoursSun' => 'unit_hoursSun',
            'additionalInformation' => 'unit_additionalInfo',
        ];

        $command = UpdateForResponsibilities::create($data);

        $this->assertEquals(999, $command->getId());
        $this->assertEquals(888, $command->getVersion());

        $this->assertEquals('unit_tmType', $command->getTmType());
        $this->assertEquals('unit_isOwner', $command->getIsOwner());
        $this->assertEquals('unit_hoursMon', $command->getHoursMon());
        $this->assertEquals('unit_hoursTue', $command->getHoursTue());
        $this->assertEquals('unit_hoursWed', $command->getHoursWed());
        $this->assertEquals('unit_hoursThu', $command->getHoursThu());
        $this->assertEquals('unit_hoursFri', $command->getHoursFri());
        $this->assertEquals('unit_hoursSat', $command->getHoursSat());
        $this->assertEquals('unit_hoursSun', $command->getHoursSun());
        $this->assertEquals('unit_additionalInfo', $command->getAdditionalInformation());
    }
}
