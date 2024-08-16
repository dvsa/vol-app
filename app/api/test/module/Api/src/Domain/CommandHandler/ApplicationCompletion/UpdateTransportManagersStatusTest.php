<?php

/**
 * Update Transport Managers Status Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\ApplicationCompletion;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Command\ApplicationCompletion\UpdateTransportManagersStatus as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationCompletion\UpdateTransportManagersStatus;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion as ApplicationCompletionEntity;

/**
 * Update Transport Managers Status Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class UpdateTransportManagersStatusTest extends AbstractUpdateStatusTestCase
{
    protected $section = 'TransportManagers';

    public function setUp(): void
    {
        $this->sut = new UpdateTransportManagersStatus();
        $this->command = Cmd::create(['id' => 111]);

        parent::setUp();
    }

    public function initReferences()
    {
        $this->refData = [
            Licence::LICENCE_TYPE_STANDARD_NATIONAL,
            Licence::LICENCE_TYPE_RESTRICTED
        ];

        parent::initReferences();
    }

    public function testHandleCommandWithChange()
    {
        $this->applicationCompletion->setTransportManagersStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->application->setLicenceType($this->refData[Licence::LICENCE_TYPE_STANDARD_NATIONAL]);
        $this->application->setTransportManagers(new ArrayCollection());

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandWithoutChange()
    {
        $this->applicationCompletion->setTransportManagersStatus(ApplicationCompletionEntity::STATUS_INCOMPLETE);

        $this->application->setLicenceType($this->refData[Licence::LICENCE_TYPE_STANDARD_NATIONAL]);
        $this->application->setTransportManagers(new ArrayCollection());

        $this->expectStatusUnchanged(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommand()
    {
        $this->applicationCompletion->setTransportManagersStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->application->setLicenceType($this->refData[Licence::LICENCE_TYPE_STANDARD_NATIONAL]);

        $this->application->setTransportManagers(new ArrayCollection(['foo']));

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_COMPLETE);
    }

    public function testHandleCommandRestricted()
    {
        $this->applicationCompletion->setTransportManagersStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->application->setLicenceType($this->refData[Licence::LICENCE_TYPE_RESTRICTED]);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_COMPLETE);
    }
}
