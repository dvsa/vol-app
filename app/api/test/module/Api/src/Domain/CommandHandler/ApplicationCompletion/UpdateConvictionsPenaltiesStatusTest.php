<?php

/**
 * Update Convictions Penalties Status Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\ApplicationCompletion;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\ApplicationCompletion\UpdateConvictionsPenaltiesStatus as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationCompletion\UpdateConvictionsPenaltiesStatus;
use Mockery as m;
use Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion as ApplicationCompletionEntity;

/**
 * Update Convictions Penalties Status Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class UpdateConvictionsPenaltiesStatusTest extends AbstractUpdateStatusTestCase
{
    protected $section = 'ConvictionsPenalties';

    public function setUp(): void
    {
        $this->sut = new UpdateConvictionsPenaltiesStatus();
        $this->command = Cmd::create(['id' => 111]);

        parent::setUp();
    }

    public function testHandleCommandWithChange()
    {
        $this->applicationCompletion->setConvictionsPenaltiesStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandWithoutChange()
    {
        $this->applicationCompletion->setConvictionsPenaltiesStatus(ApplicationCompletionEntity::STATUS_INCOMPLETE);

        $this->expectStatusUnchanged(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandUnconfirmed()
    {
        $this->applicationCompletion->setConvictionsPenaltiesStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->application->setPrevConviction('Y');

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandNoConvictions()
    {
        $this->applicationCompletion->setConvictionsPenaltiesStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->application->setPrevConviction('Y');
        $this->application->setConvictionsConfirmation('Y');
        $this->application->setPreviousConvictions(new ArrayCollection());

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandNoConvictionsRequired()
    {
        $this->applicationCompletion->setConvictionsPenaltiesStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->application->setPrevConviction('N');
        $this->application->setConvictionsConfirmation('Y');
        $this->application->setPreviousConvictions(new ArrayCollection());

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_COMPLETE);
    }

    public function testHandleCommand()
    {
        $this->applicationCompletion->setConvictionsPenaltiesStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->application->setPrevConviction('Y');
        $this->application->setPreviousConvictions(new ArrayCollection(['foo']));
        $this->application->setConvictionsConfirmation('Y');

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_COMPLETE);
    }
}
