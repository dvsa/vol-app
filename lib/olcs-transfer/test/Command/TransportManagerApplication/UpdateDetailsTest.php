<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Command\TransportManagerApplication;

use Dvsa\Olcs\Transfer\Command\TransportManagerApplication\UpdateDetails;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Command\TransportManagerApplication\UpdateDetails::class)]
final class UpdateDetailsTest extends \PHPUnit\Framework\TestCase
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

        $this->assertEquals(999, $command->getId());
        $this->assertEquals(888, $command->getVersion());

        $this->assertEquals('unit_email', $command->getEmail());
        $this->assertEquals('unit_placeOfBirth', $command->getPlaceOfBirth());
        $this->assertEquals('unit_lgvArRefNumber', $command->getLgvAcquiredRightsReferenceNumber());
        $this->assertEquals('unit_homeAddress', $command->getHomeAddress());
        $this->assertEquals('unit_workAddress', $command->getWorkAddress());
        $this->assertEquals('unit_tmType', $command->getTmType());
        $this->assertEquals('unit_isOwner', $command->getIsOwner());
        $this->assertEquals('unit_hoursMon', $command->getHoursMon());
        $this->assertEquals('unit_hoursTue', $command->getHoursTue());
        $this->assertEquals('unit_hoursWed', $command->getHoursWed());
        $this->assertEquals('unit_hoursThu', $command->getHoursThu());
        $this->assertEquals('unit_hoursFri', $command->getHoursFri());
        $this->assertEquals('unit_hoursSat', $command->getHoursSat());
        $this->assertEquals('unit_hoursSun', $command->getHoursSun());
        $this->assertEquals('has_other_licences', $command->getHasOtherLicences());
        $this->assertEquals('has_other_employment', $command->getHasOtherEmployment());
        $this->assertEquals('has_convictions', $command->getHasConvictions());
        $this->assertEquals('has_previous_licences', $command->getHasPreviousLicences());
        $this->assertEquals('Y', $command->getHasUndertakenTraining());
        $this->assertEquals('unit_additionalInfo', $command->getAdditionalInfo());
        $this->assertEquals('unit_dob', $command->getDob());
        $this->assertEquals('unit_submit', $command->getSubmit());
    }
}
