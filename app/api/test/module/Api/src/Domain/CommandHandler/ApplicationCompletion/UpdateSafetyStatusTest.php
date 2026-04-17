<?php

declare(strict_types=1);

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
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class UpdateSafetyStatusTest extends AbstractUpdateStatusTestCase
{
    protected $section = 'Safety';

    public function setUp(): void
    {
        $this->sut = new UpdateSafetyStatus();
        $this->command = Cmd::create(['id' => 111]);

        parent::setUp();
    }

    #[\Override]
    public function initReferences(): void
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

    public function testHandleCommandWithChange(): void
    {
        $this->applicationCompletion->setSafetyStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandWithoutChange(): void
    {
        $this->applicationCompletion->setSafetyStatus(ApplicationCompletionEntity::STATUS_INCOMPLETE);

        $this->expectStatusUnchanged(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandWithoutInsVaries(): void
    {
        $this->applicationCompletion->setSafetyStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->licence->setSafetyInsVehicles(1);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandWithoutTachoIns(): void
    {
        $this->applicationCompletion->setSafetyStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->licence->setSafetyInsVehicles(1);
        $this->licence->setSafetyInsVaries(1);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandWithoutWorkshops(): void
    {
        $this->applicationCompletion->setSafetyStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->licence->setSafetyInsVehicles(1);
        $this->licence->setSafetyInsVaries(1);
        $this->licence->setTachographIns($this->refData[Licence::TACH_EXT]);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandWithoutConfirmation(): void
    {
        $this->applicationCompletion->setSafetyStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->licence->setSafetyInsVehicles(1);
        $this->licence->setSafetyInsVaries(1);
        $this->licence->setTachographIns($this->refData[Licence::TACH_EXT]);
        $this->licence->setWorkshops(['foo']);
        $this->application->setSafetyConfirmation('N');

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandWithoutTachoName(): void
    {
        $this->applicationCompletion->setSafetyStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->licence->setSafetyInsVehicles(1);
        $this->licence->setSafetyInsVaries(1);
        $this->licence->setTachographIns($this->refData[Licence::TACH_EXT]);
        $this->licence->setWorkshops(['foo']);
        $this->application->setSafetyConfirmation('Y');

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public static function dpHandleCommandTrailers(): array
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHandleCommandTrailers')]
    public function testHandleCommandTrailers(mixed $vehicleType, mixed $safetyInsTrailers, mixed $expected): void
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
