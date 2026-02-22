<?php

declare(strict_types=1);

/**
 * Update Business Details Status Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\ApplicationCompletion;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\ApplicationCompletion\UpdateBusinessDetailsStatus as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationCompletion\UpdateBusinessDetailsStatus;
use Mockery as m;
use Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion as ApplicationCompletionEntity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;

/**
 * Update Business Details Status Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class UpdateBusinessDetailsStatusTest extends AbstractUpdateStatusTestCase
{
    /**
     * @var Organisation
     */
    protected $organisation;

    protected $section = 'BusinessDetails';

    public function setUp(): void
    {
        $this->sut = new UpdateBusinessDetailsStatus();
        $this->command = Cmd::create(['id' => 111]);

        parent::setUp();

        $this->organisation = m::mock(Organisation::class)->makePartial();
        $this->licence->setOrganisation($this->organisation);
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            Organisation::ORG_TYPE_REGISTERED_COMPANY,
            Organisation::ORG_TYPE_PARTNERSHIP,
            Organisation::ORG_TYPE_SOLE_TRADER
        ];

        parent::initReferences();
    }

    public function testHandleCommandNoTypeWithChange(): void
    {
        $this->applicationCompletion->setBusinessDetailsStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandNoTypeWithoutChange(): void
    {
        $this->applicationCompletion->setBusinessDetailsStatus(ApplicationCompletionEntity::STATUS_INCOMPLETE);

        $this->expectStatusUnchanged(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandWithoutRequiredName(): void
    {
        $this->applicationCompletion->setBusinessDetailsStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->organisation->setType($this->refData[Organisation::ORG_TYPE_REGISTERED_COMPANY]);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandWithoutCompanyNo(): void
    {
        $this->applicationCompletion->setBusinessDetailsStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->organisation->setType($this->refData[Organisation::ORG_TYPE_REGISTERED_COMPANY]);
        $this->organisation->setName('Foo ltd');

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandWithoutContactDetails(): void
    {
        $this->applicationCompletion->setBusinessDetailsStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->organisation->setType($this->refData[Organisation::ORG_TYPE_REGISTERED_COMPANY]);
        $this->organisation->setName('Foo ltd');
        $this->organisation->setCompanyOrLlpNo('12345678');

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommand(): void
    {
        $this->applicationCompletion->setBusinessDetailsStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $contactDetails = m::mock(ContactDetails::class)->makePartial();

        $this->organisation->setType($this->refData[Organisation::ORG_TYPE_REGISTERED_COMPANY]);
        $this->organisation->setName('Foo ltd');
        $this->organisation->setCompanyOrLlpNo('12345678');
        $this->organisation->setContactDetails($contactDetails);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_COMPLETE);
    }

    public function testHandleCommandPartnership(): void
    {
        $this->applicationCompletion->setBusinessDetailsStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->organisation->setType($this->refData[Organisation::ORG_TYPE_PARTNERSHIP]);
        $this->organisation->setName('Foo ltd');

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_COMPLETE);
    }

    public function testHandleCommandSoleTrader(): void
    {
        $this->applicationCompletion->setBusinessDetailsStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->organisation->setType($this->refData[Organisation::ORG_TYPE_SOLE_TRADER]);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_COMPLETE);
    }
}
