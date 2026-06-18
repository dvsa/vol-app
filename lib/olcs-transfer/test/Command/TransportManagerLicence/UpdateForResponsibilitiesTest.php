<?php

namespace Dvsa\OlcsTest\Transfer\Command\TransportManagerLicence;

use Dvsa\Olcs\Transfer\Command\TransportManagerLicence\UpdateForResponsibilities;

/**
 * @covers \Dvsa\Olcs\Transfer\Command\TransportManagerLicence\UpdateForResponsibilities
 */
class UpdateForResponsibilitiesTest extends \PHPUnit\Framework\TestCase
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

        static::assertEquals(999, $command->getId());
        static::assertEquals(888, $command->getVersion());

        static::assertEquals('unit_tmType', $command->getTmType());
        static::assertEquals('unit_isOwner', $command->getIsOwner());
        static::assertEquals('unit_hoursMon', $command->getHoursMon());
        static::assertEquals('unit_hoursTue', $command->getHoursTue());
        static::assertEquals('unit_hoursWed', $command->getHoursWed());
        static::assertEquals('unit_hoursThu', $command->getHoursThu());
        static::assertEquals('unit_hoursFri', $command->getHoursFri());
        static::assertEquals('unit_hoursSat', $command->getHoursSat());
        static::assertEquals('unit_hoursSun', $command->getHoursSun());
        static::assertEquals('unit_additionalInfo', $command->getAdditionalInformation());
    }
}
