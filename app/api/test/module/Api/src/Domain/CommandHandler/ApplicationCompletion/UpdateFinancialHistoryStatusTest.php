<?php

declare(strict_types=1);

/**
 * Update Financial History Status Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\ApplicationCompletion;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\ApplicationCompletion\UpdateFinancialHistoryStatus as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationCompletion\UpdateFinancialHistoryStatus;
use Mockery as m;
use Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion as ApplicationCompletionEntity;

/**
 * Update Financial History Status Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class UpdateFinancialHistoryStatusTest extends AbstractUpdateStatusTestCase
{
    protected $section = 'FinancialHistory';

    public function setUp(): void
    {
        $this->sut = new UpdateFinancialHistoryStatus();
        $this->command = Cmd::create(['id' => 111]);

        parent::setUp();
    }

    public function testHandleCommandWithChange(): void
    {
        $this->applicationCompletion->setFinancialHistoryStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandWithoutChange(): void
    {
        $this->applicationCompletion->setFinancialHistoryStatus(ApplicationCompletionEntity::STATUS_INCOMPLETE);

        $this->expectStatusUnchanged(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandUnconfirmed(): void
    {
        $this->applicationCompletion->setFinancialHistoryStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->application->setInsolvencyConfirmation('N');

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandUnansweredQuestions(): void
    {
        $this->applicationCompletion->setFinancialHistoryStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->application->setInsolvencyConfirmation('Y');

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandShortInsolvencyDetails(): void
    {
        $this->applicationCompletion->setFinancialHistoryStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->application->setInsolvencyConfirmation('Y');

        $this->application->setBankrupt('Y');
        $this->application->setLiquidation('Y');
        $this->application->setReceivership('Y');
        $this->application->setAdministration('Y');
        $this->application->setDisqualified('Y');

        $this->application->setInsolvencyDetails('Foo');

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandWithLongDetails(): void
    {
        $this->applicationCompletion->setFinancialHistoryStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->application->setInsolvencyConfirmation('Y');

        $this->application->setBankrupt('Y');
        $this->application->setLiquidation('Y');
        $this->application->setReceivership('Y');
        $this->application->setAdministration('Y');
        $this->application->setDisqualified('Y');

        $this->application->setInsolvencyDetails(str_repeat('a', 200));

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_COMPLETE);
    }

    public function testHandleCommandWithNos(): void
    {
        $this->applicationCompletion->setFinancialHistoryStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->application->setInsolvencyConfirmation('Y');

        $this->application->setBankrupt('N');
        $this->application->setLiquidation('N');
        $this->application->setReceivership('N');
        $this->application->setAdministration('N');
        $this->application->setDisqualified('N');

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_COMPLETE);
    }
}
