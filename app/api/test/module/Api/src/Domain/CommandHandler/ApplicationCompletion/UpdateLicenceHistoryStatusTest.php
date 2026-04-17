<?php

declare(strict_types=1);

/**
 * Update Licence History Status Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\ApplicationCompletion;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\ApplicationCompletion\UpdateLicenceHistoryStatus as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationCompletion\UpdateLicenceHistoryStatus;
use Mockery as m;
use Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion as ApplicationCompletionEntity;
use Dvsa\Olcs\Api\Entity\OtherLicence\OtherLicence;

/**
 * Update Licence History Status Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class UpdateLicenceHistoryStatusTest extends AbstractUpdateStatusTestCase
{
    protected $section = 'LicenceHistory';

    public function setUp(): void
    {
        $this->sut = new UpdateLicenceHistoryStatus();
        $this->command = Cmd::create(['id' => 111]);

        parent::setUp();
    }

    #[\Override]
    public function initReferences(): void
    {
        $this->refData = [
            OtherLicence::TYPE_DISQUALIFIED,
            OtherLicence::TYPE_CURRENT
        ];

        parent::initReferences();
    }

    public function testHandleCommandWithChange(): void
    {
        $this->applicationCompletion->setLicenceHistoryStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandWithoutChange(): void
    {
        $this->applicationCompletion->setLicenceHistoryStatus(ApplicationCompletionEntity::STATUS_INCOMPLETE);

        $this->expectStatusUnchanged(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandWithoutAnAnswer(): void
    {
        $this->applicationCompletion->setLicenceHistoryStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->application->setPrevHasLicence(null);
        $this->application->setPrevHadLicence('Y');
        $this->application->setPrevBeenRefused('Y');
        $this->application->setPrevBeenRevoked('Y');
        $this->application->setPrevBeenDisqualifiedTc('Y');
        $this->application->setPrevBeenAtPi('Y');
        $this->application->setPrevPurchasedAssets('Y');

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandWithAllNos(): void
    {
        $this->applicationCompletion->setLicenceHistoryStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->application->setPrevHasLicence('N');
        $this->application->setPrevHadLicence('N');
        $this->application->setPrevBeenRefused('N');
        $this->application->setPrevBeenRevoked('N');
        $this->application->setPrevBeenDisqualifiedTc('N');
        $this->application->setPrevBeenAtPi('N');
        $this->application->setPrevPurchasedAssets('N');

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_COMPLETE);
    }

    public function testHandleCommandWithYesWithoutOtherLicences(): void
    {
        $this->applicationCompletion->setLicenceHistoryStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->application->setOtherLicences([]);

        $this->application->setPrevHasLicence('Y');
        $this->application->setPrevHadLicence('N');
        $this->application->setPrevBeenRefused('N');
        $this->application->setPrevBeenRevoked('N');
        $this->application->setPrevBeenDisqualifiedTc('N');
        $this->application->setPrevBeenAtPi('N');
        $this->application->setPrevPurchasedAssets('N');

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandWithYesWithoutSpecificOtherLicences(): void
    {
        $this->applicationCompletion->setLicenceHistoryStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        /** @var OtherLicence $otherLicence */
        $otherLicence = m::mock(OtherLicence::class)->makePartial();
        $otherLicence->setPreviousLicenceType(
            $this->refData[OtherLicence::TYPE_DISQUALIFIED]
        );

        $otherLicences = [
            $otherLicence
        ];

        $this->application->setOtherLicences($otherLicences);

        $this->application->setPrevHasLicence('Y');
        $this->application->setPrevHadLicence('N');
        $this->application->setPrevBeenRefused('N');
        $this->application->setPrevBeenRevoked('N');
        $this->application->setPrevBeenDisqualifiedTc('N');
        $this->application->setPrevBeenAtPi('N');
        $this->application->setPrevPurchasedAssets('N');

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandWithYesWithSpecificOtherLicences(): void
    {
        $this->applicationCompletion->setLicenceHistoryStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        /** @var OtherLicence $otherLicence */
        $otherLicence = m::mock(OtherLicence::class)->makePartial();
        $otherLicence->setPreviousLicenceType(
            $this->refData[OtherLicence::TYPE_CURRENT]
        );

        $otherLicences = [
            $otherLicence
        ];

        $this->application->setOtherLicences($otherLicences);

        $this->application->setPrevHasLicence('Y');
        $this->application->setPrevHadLicence('N');
        $this->application->setPrevBeenRefused('N');
        $this->application->setPrevBeenRevoked('N');
        $this->application->setPrevBeenDisqualifiedTc('N');
        $this->application->setPrevBeenAtPi('N');
        $this->application->setPrevPurchasedAssets('N');

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_COMPLETE);
    }
}
