<?php

/**
 * Update Safety Status Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\ApplicationCompletion;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\ApplicationCompletion\UpdateSafetyStatus as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationCompletion\UpdateSafetyStatus;
use Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion as ApplicationCompletionEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Mockery as m;

/**
 * Update Safety Status Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class UpdateSafetyStatusTest extends AbstractUpdateStatusTestCase
{
    protected $section = 'Safety';

    public function setUp(): void
    {
        $this->sut = new UpdateSafetyStatus();
        $this->command = Cmd::create(['id' => 111]);

        parent::setUp();
    }

    public function initReferences()
    {
        $this->refData = [
            Licence::TACH_EXT,
            RefData::APP_VEHICLE_TYPE_MIXED,
            RefData::APP_VEHICLE_TYPE_LGV,
            RefData::APP_VEHICLE_TYPE_HGV,
            RefData::APP_VEHICLE_TYPE_PSV,
        ];

        parent::initReferences();
    }

    public function testHandleCommandWithChange()
    {
        $this->applicationCompletion->setSafetyStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandWithoutChange()
    {
        $this->applicationCompletion->setSafetyStatus(ApplicationCompletionEntity::STATUS_INCOMPLETE);

        $this->expectStatusUnchanged(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandWithoutInsVaries()
    {
        $this->applicationCompletion->setSafetyStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->licence->setSafetyInsVehicles(1);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandWithoutTachoIns()
    {
        $this->applicationCompletion->setSafetyStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->licence->setSafetyInsVehicles(1);
        $this->licence->setSafetyInsVaries(1);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandWithoutWorkshops()
    {
        $this->applicationCompletion->setSafetyStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->licence->setSafetyInsVehicles(1);
        $this->licence->setSafetyInsVaries(1);
        $this->licence->setTachographIns($this->refData[Licence::TACH_EXT]);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandWithoutConfirmation()
    {
        $this->applicationCompletion->setSafetyStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->licence->setSafetyInsVehicles(1);
        $this->licence->setSafetyInsVaries(1);
        $this->licence->setTachographIns($this->refData[Licence::TACH_EXT]);
        $this->licence->setWorkshops(['foo']);
        $this->application->setSafetyConfirmation('N');

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandWithoutTachoName()
    {
        $this->applicationCompletion->setSafetyStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->licence->setSafetyInsVehicles(1);
        $this->licence->setSafetyInsVaries(1);
        $this->licence->setTachographIns($this->refData[Licence::TACH_EXT]);
        $this->licence->setWorkshops(['foo']);
        $this->application->setSafetyConfirmation('Y');

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function dpHandleCommandTrailers()
    {
        return [
            [
                'vehicleType' => RefData::APP_VEHICLE_TYPE_MIXED,
                'safetyInsTrailers' => null,
                'expected' => ApplicationCompletionEntity::STATUS_INCOMPLETE,
            ],
            [
                'vehicleType' => RefData::APP_VEHICLE_TYPE_MIXED,
                'safetyInsTrailers' => 1,
                'expected' => ApplicationCompletionEntity::STATUS_COMPLETE,
            ],
            [
                'vehicleType' => RefData::APP_VEHICLE_TYPE_HGV,
                'safetyInsTrailers' => null,
                'expected' => ApplicationCompletionEntity::STATUS_INCOMPLETE,
            ],
            [
                'vehicleType' => RefData::APP_VEHICLE_TYPE_HGV,
                'safetyInsTrailers' => 1,
                'expected' => ApplicationCompletionEntity::STATUS_COMPLETE,
            ],
            [
                'vehicleType' => RefData::APP_VEHICLE_TYPE_LGV,
                'safetyInsTrailers' => null,
                'expected' => ApplicationCompletionEntity::STATUS_COMPLETE,
            ],
            [
                'vehicleType' => RefData::APP_VEHICLE_TYPE_PSV,
                'safetyInsTrailers' => null,
                'expected' => ApplicationCompletionEntity::STATUS_COMPLETE,
            ],
        ];
    }

    /**
     * @dataProvider dpHandleCommandTrailers
     */
    public function testHandleCommandTrailers($vehicleType, $safetyInsTrailers, $expected)
    {
        $this->applicationCompletion->setSafetyStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->application->setVehicleType($this->refData[$vehicleType]);

        $this->licence->setSafetyInsVehicles(1);
        $this->licence->setSafetyInsVaries('Y');
        $this->licence->setTachographIns($this->refData[Licence::TACH_EXT]);
        $this->licence->setWorkshops(['foo']);
        $this->application->setSafetyConfirmation('Y');
        $this->licence->setTachographInsName('Foo');
        $this->licence->setSafetyInsTrailers($safetyInsTrailers);

        $this->expectStatusChange($expected);
    }
}
