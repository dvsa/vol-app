<?php

declare(strict_types=1);

/**
 * Update Business Type Status Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\ApplicationCompletion;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\ApplicationCompletion\UpdateBusinessTypeStatus as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationCompletion\UpdateBusinessTypeStatus;
use Mockery as m;
use Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion as ApplicationCompletionEntity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;

/**
 * Update Business Type Status Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class UpdateBusinessTypeStatusTest extends AbstractUpdateStatusTestCase
{
    /**
     * @var Organisation
     */
    protected $organisation;

    protected $section = 'BusinessType';

    public function setUp(): void
    {
        $this->sut = new UpdateBusinessTypeStatus();
        $this->command = Cmd::create(['id' => 111]);

        parent::setUp();

        $this->organisation = m::mock(Organisation::class)->makePartial();
        $this->licence->setOrganisation($this->organisation);
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            Organisation::ORG_TYPE_REGISTERED_COMPANY
        ];

        parent::initReferences();
    }

    public function testHandleCommandNoTypeWithChange(): void
    {
        $this->applicationCompletion->setBusinessTypeStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandNoTypeWithoutChange(): void
    {
        $this->applicationCompletion->setBusinessTypeStatus(ApplicationCompletionEntity::STATUS_INCOMPLETE);

        $this->expectStatusUnchanged(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommand(): void
    {
        $this->applicationCompletion->setBusinessTypeStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->organisation->setType($this->refData[Organisation::ORG_TYPE_REGISTERED_COMPANY]);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_COMPLETE);
    }
}
