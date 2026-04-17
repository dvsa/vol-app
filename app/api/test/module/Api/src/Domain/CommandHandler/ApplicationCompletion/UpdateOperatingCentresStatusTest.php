<?php

declare(strict_types=1);

/**
 * Update Operating Centres Status Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\ApplicationCompletion;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\ApplicationCompletion\UpdateOperatingCentresStatus as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationCompletion\UpdateOperatingCentresStatus;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Mockery as m;
use Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion as ApplicationCompletionEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;

/**
 * Update Operating Centres Status Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class UpdateOperatingCentresStatusTest extends AbstractUpdateStatusTestCase
{
    protected $section = 'OperatingCentres';

    public function setUp(): void
    {
        $this->sut = new UpdateOperatingCentresStatus();
        $this->command = Cmd::create(['id' => 111]);

        parent::setUp();
    }

    #[\Override]
    public function initReferences(): void
    {
        $this->refData = [
            RefData::APP_VEHICLE_TYPE_HGV,
            RefData::APP_VEHICLE_TYPE_LGV,
            RefData::APP_VEHICLE_TYPE_MIXED,
            RefData::APP_VEHICLE_TYPE_PSV,
        ];

        parent::initReferences();
    }

    public function testHandleCommandWithChange(): void
    {
        $this->applicationCompletion->setOperatingCentresStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->application->setVehicleType($this->refData[RefData::APP_VEHICLE_TYPE_HGV]);
        $this->application->setOperatingCentres([]);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandWithoutChange(): void
    {
        $this->applicationCompletion->setOperatingCentresStatus(ApplicationCompletionEntity::STATUS_INCOMPLETE);

        $this->application->setVehicleType($this->refData[RefData::APP_VEHICLE_TYPE_HGV]);
        $this->application->setOperatingCentres([]);

        $this->expectStatusUnchanged(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandHgvNoTotAuth(): void
    {
        $this->applicationCompletion->setOperatingCentresStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->application->setVehicleType($this->refData[RefData::APP_VEHICLE_TYPE_HGV]);
        $this->application->setOperatingCentres(['foo']);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandHgvNoTotAuthTrailers(): void
    {
        $this->applicationCompletion->setOperatingCentresStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->application->setVehicleType($this->refData[RefData::APP_VEHICLE_TYPE_HGV]);
        $this->application->setOperatingCentres(['foo']);
        $this->application->updateTotAuthHgvVehicles(10);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandHgv(): void
    {
        $this->applicationCompletion->setOperatingCentresStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->application->setVehicleType($this->refData[RefData::APP_VEHICLE_TYPE_HGV]);
        $this->application->setOperatingCentres(['foo']);
        $this->application->updateTotAuthHgvVehicles(10);
        $this->application->setTotAuthTrailers(0);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_COMPLETE);
    }

    public function testHandleCommandLgv(): void
    {
        $this->applicationCompletion->setOperatingCentresStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->application->setVehicleType($this->refData[RefData::APP_VEHICLE_TYPE_LGV]);
        $this->application->updateTotAuthLgvVehicles(10);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_COMPLETE);
    }

    public function testHandleCommandMixed(): void
    {
        $this->applicationCompletion->setOperatingCentresStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->application->setVehicleType($this->refData[RefData::APP_VEHICLE_TYPE_MIXED]);
        $this->application->setOperatingCentres(['foo']);
        $this->application->updateTotAuthHgvVehicles(10);
        $this->application->updateTotAuthLgvVehicles(0);
        $this->application->setTotAuthTrailers(0);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_COMPLETE);
    }

    public function testHandleCommandPsv(): void
    {
        $this->applicationCompletion->setOperatingCentresStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->application->setVehicleType($this->refData[RefData::APP_VEHICLE_TYPE_PSV]);
        $this->application->setOperatingCentres(['foo']);
        $this->application->updateTotAuthHgvVehicles(10);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_COMPLETE);
    }
}
