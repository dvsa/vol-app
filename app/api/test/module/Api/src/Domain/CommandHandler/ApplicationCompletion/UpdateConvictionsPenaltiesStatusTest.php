<?php

declare(strict_types=1);

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
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class UpdateConvictionsPenaltiesStatusTest extends AbstractUpdateStatusTestCase
{
    protected $section = 'ConvictionsPenalties';

    public function setUp(): void
    {
        $this->sut = new UpdateConvictionsPenaltiesStatus();
        $this->command = Cmd::create(['id' => 111]);

        parent::setUp();
    }

    public function testHandleCommandWithChange(): void
    {
        $this->applicationCompletion->setConvictionsPenaltiesStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandWithoutChange(): void
    {
        $this->applicationCompletion->setConvictionsPenaltiesStatus(ApplicationCompletionEntity::STATUS_INCOMPLETE);

        $this->expectStatusUnchanged(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandUnconfirmed(): void
    {
        $this->applicationCompletion->setConvictionsPenaltiesStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->application->setPrevConviction('Y');

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandNoConvictions(): void
    {
        $this->applicationCompletion->setConvictionsPenaltiesStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->application->setPrevConviction('Y');
        $this->application->setConvictionsConfirmation('Y');
        $this->application->setPreviousConvictions(new ArrayCollection());

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandNoConvictionsRequired(): void
    {
        $this->applicationCompletion->setConvictionsPenaltiesStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->application->setPrevConviction('N');
        $this->application->setConvictionsConfirmation('Y');
        $this->application->setPreviousConvictions(new ArrayCollection());

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_COMPLETE);
    }

    public function testHandleCommand(): void
    {
        $this->applicationCompletion->setConvictionsPenaltiesStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->application->setPrevConviction('Y');
        $this->application->setPreviousConvictions(new ArrayCollection(['foo']));
        $this->application->setConvictionsConfirmation('Y');

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_COMPLETE);
    }
}
