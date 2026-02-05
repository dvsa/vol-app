<?php

declare(strict_types=1);

/**
 * Update Taxi Phv Status Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\ApplicationCompletion;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\ApplicationCompletion\UpdateTaxiPhvStatus as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationCompletion\UpdateTaxiPhvStatus;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Mockery as m;
use Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion as ApplicationCompletionEntity;

/**
 * Update Taxi Phv Status Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class UpdateTaxiPhvStatusTest extends AbstractUpdateStatusTestCase
{
    protected $section = 'TaxiPhv';

    public function setUp(): void
    {
        $this->sut = new UpdateTaxiPhvStatus();
        $this->command = Cmd::create(['id' => 111]);

        parent::setUp();
    }

    public function testHandleCommandWithChange(): void
    {
        $this->applicationCompletion->setTaxiPhvStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);
        $this->licence->setPrivateHireLicences(new ArrayCollection(['foo']));

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandWithoutChange(): void
    {
        $this->applicationCompletion->setTaxiPhvStatus(ApplicationCompletionEntity::STATUS_INCOMPLETE);
        $this->licence->setPrivateHireLicences(new ArrayCollection());

        $this->expectStatusUnchanged(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandNoTrafficArea(): void
    {
        $this->applicationCompletion->setTaxiPhvStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->licence->setPrivateHireLicences(new ArrayCollection(['foo']));

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandNoPhl(): void
    {
        $this->applicationCompletion->setTaxiPhvStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->licence->setPrivateHireLicences(new ArrayCollection());
        $this->licence->setTrafficArea('bar');

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommand(): void
    {
        $this->applicationCompletion->setTaxiPhvStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->licence->setPrivateHireLicences(new ArrayCollection(['foo']));
        $this->licence->setTrafficArea('bar');

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_COMPLETE);
    }
}
