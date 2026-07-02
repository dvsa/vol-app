<?php

namespace Dvsa\OlcsTest\Transfer\Command\TransportManagerApplication;

use Dvsa\Olcs\Transfer\Command\TransportManagerApplication\UpdateDetails;

/**
 * @covers \Dvsa\Olcs\Transfer\Command\TransportManagerApplication\UpdateDetails
 */
class UpdateDetailsTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $data = [
            'id' => 999,
            'version' => 888,
            'email' => 'unit_email',
            'placeOfBirth' => 'unit_placeOfBirth',
            'lgvAcquiredRightsReferenceNumber' => 'unit_lgvArRefNumber',
            'homeAddress' => 'unit_homeAddress',
            'workAddress' => 'unit_workAddress',
            'tmType' => 'unit_tmType',
            'isOwner' => 'unit_isOwner',
            'hoursMon' => 'unit_hoursMon',
            'hoursTue' => 'unit_hoursTue',
            'hoursWed' => 'unit_hoursWed',
            'hoursThu' => 'unit_hoursThu',
            'hoursFri' => 'unit_hoursFri',
            'hoursSat' => 'unit_hoursSat',
            'hoursSun' => 'unit_hoursSun',
            'hasOtherLicences' => 'has_other_licences',
            'hasOtherEmployment' => 'has_other_employment',
            'hasConvictions' => 'has_convictions',
            'hasPreviousLicences' => 'has_previous_licences',
            'hasUndertakenTraining' => 'Y',
            'additionalInfo' => 'unit_additionalInfo',
            'dob' => 'unit_dob',
            'submit' => 'unit_submit',
        ];

        $command = UpdateDetails::create($data);

        static::assertEquals(999, $command->getId());
        static::assertEquals(888, $command->getVersion());

        static::assertEquals('unit_email', $command->getEmail());
        static::assertEquals('unit_placeOfBirth', $command->getPlaceOfBirth());
        static::assertEquals('unit_lgvArRefNumber', $command->getLgvAcquiredRightsReferenceNumber());
        static::assertEquals('unit_homeAddress', $command->getHomeAddress());
        static::assertEquals('unit_workAddress', $command->getWorkAddress());
        static::assertEquals('unit_tmType', $command->getTmType());
        static::assertEquals('unit_isOwner', $command->getIsOwner());
        static::assertEquals('unit_hoursMon', $command->getHoursMon());
        static::assertEquals('unit_hoursTue', $command->getHoursTue());
        static::assertEquals('unit_hoursWed', $command->getHoursWed());
        static::assertEquals('unit_hoursThu', $command->getHoursThu());
        static::assertEquals('unit_hoursFri', $command->getHoursFri());
        static::assertEquals('unit_hoursSat', $command->getHoursSat());
        static::assertEquals('unit_hoursSun', $command->getHoursSun());
        static::assertEquals('has_other_licences', $command->getHasOtherLicences());
        static::assertEquals('has_other_employment', $command->getHasOtherEmployment());
        static::assertEquals('has_convictions', $command->getHasConvictions());
        static::assertEquals('has_previous_licences', $command->getHasPreviousLicences());
        static::assertEquals('Y', $command->getHasUndertakenTraining());
        static::assertEquals('unit_additionalInfo', $command->getAdditionalInfo());
        static::assertEquals('unit_dob', $command->getDob());
        static::assertEquals('unit_submit', $command->getSubmit());
    }
}
