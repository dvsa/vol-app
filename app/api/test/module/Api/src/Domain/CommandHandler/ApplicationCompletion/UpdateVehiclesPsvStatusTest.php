<?php

/**
 * Update Vehicles Psv Status Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\ApplicationCompletion;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\ApplicationCompletion\UpdateVehiclesPsvStatus as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationCompletion\UpdateVehiclesPsvStatus;
use Mockery as m;
use Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion as ApplicationCompletionEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle;
use Dvsa\Olcs\Api\Entity\Vehicle\Vehicle;

/**
 * Update Vehicles Psv Status Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class UpdateVehiclesPsvStatusTest extends AbstractUpdateStatusTestCase
{
    protected $section = 'VehiclesPsv';

    public function setUp(): void
    {
        $this->sut = new UpdateVehiclesPsvStatus();
        $this->command = Cmd::create(['id' => 111]);

        parent::setUp();
    }

    public function initReferences()
    {
        $this->refData = [
            Licence::LICENCE_TYPE_STANDARD_NATIONAL,
            Licence::LICENCE_TYPE_RESTRICTED,
        ];

        parent::initReferences();
    }

    public function testHandleCommandWithChange()
    {
        $this->applicationCompletion->setVehiclesPsvStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->application->setHasEnteredReg('N');

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_COMPLETE);
    }

    public function testHandleCommandWithoutChange()
    {
        $this->applicationCompletion->setVehiclesPsvStatus(ApplicationCompletionEntity::STATUS_COMPLETE);

        $this->application->setHasEnteredReg('N');

        $this->expectStatusUnchanged(ApplicationCompletionEntity::STATUS_COMPLETE);
    }

    public function testHandleCommandWithoutVehicles()
    {
        $this->applicationCompletion->setVehiclesPsvStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->application->setHasEnteredReg('Y');

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandTotAuthVehiclesNotSet()
    {
        $this->applicationCompletion->setVehiclesPsvStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandTooMany()
    {
        $this->applicationCompletion->setVehiclesPsvStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->application->setLicenceType($this->refData[Licence::LICENCE_TYPE_STANDARD_NATIONAL]);

        /** @var Vehicle $vehicle1 */
        $vehicle1 = m::mock(Vehicle::class)->makePartial();
        /** @var LicenceVehicle $licenceVehicle1 */
        $licenceVehicle1 = m::mock(LicenceVehicle::class)->makePartial();
        $licenceVehicle1->setVehicle($vehicle1);

        $licenceVehicles = new ArrayCollection();
        $licenceVehicles->add($licenceVehicle1);

        $this->application->setHasEnteredReg('Y');
        $this->licence->setLicenceVehicles($licenceVehicles);
        $this->application->updateTotAuthHgvVehicles(0);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_COMPLETE);
    }

    public function testHandleCommand()
    {
        $this->applicationCompletion->setVehiclesPsvStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->application->setLicenceType($this->refData[Licence::LICENCE_TYPE_STANDARD_NATIONAL]);

        /** @var Vehicle $vehicle1 */
        $vehicle1 = m::mock(Vehicle::class)->makePartial();
        /** @var LicenceVehicle $licenceVehicle1 */
        $licenceVehicle1 = m::mock(LicenceVehicle::class)->makePartial();
        $licenceVehicle1->setVehicle($vehicle1);

        $licenceVehicles = new ArrayCollection();
        $licenceVehicles->add($licenceVehicle1);

        $this->application->setHasEnteredReg('Y');
        $this->licence->setLicenceVehicles($licenceVehicles);
        $this->application->updateTotAuthHgvVehicles(3);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_COMPLETE);
    }

    public function testHandleCommandRestricted()
    {
        $this->applicationCompletion->setVehiclesPsvStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->application->setLicenceType($this->refData[Licence::LICENCE_TYPE_RESTRICTED]);

        /** @var Vehicle $vehicle1 */
        $vehicle1 = m::mock(Vehicle::class)->makePartial();
        /* @var $licenceVehicle1 LicenceVehicle  */
        $licenceVehicle1 = m::mock(LicenceVehicle::class)->makePartial();
        $licenceVehicle1->setVehicle($vehicle1);

        $licenceVehicles = new ArrayCollection();
        $licenceVehicles->add($licenceVehicle1);

        $this->application->setHasEnteredReg('Y');
        $this->licence->setLicenceVehicles($licenceVehicles);
        $this->application->updateTotAuthHgvVehicles(3);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_COMPLETE);
    }
}
