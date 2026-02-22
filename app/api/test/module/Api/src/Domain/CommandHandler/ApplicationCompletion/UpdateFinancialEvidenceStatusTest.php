<?php

declare(strict_types=1);

/**
 * Update Financial Evidence Status Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\ApplicationCompletion;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\ApplicationCompletion\UpdateFinancialEvidenceStatus as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationCompletion\UpdateFinancialEvidenceStatus;
use Mockery as m;
use Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion as ApplicationCompletionEntity;

/**
 * Update Financial Evidence Status Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class UpdateFinancialEvidenceStatusTest extends AbstractUpdateStatusTestCase
{
    protected $section = 'FinancialEvidence';

    public function setUp(): void
    {
        $this->sut = new UpdateFinancialEvidenceStatus();
        $this->command = Cmd::create(['id' => 111]);

        parent::setUp();
    }

    public function testHandleCommandWithChange(): void
    {
        $this->applicationCompletion->setFinancialEvidenceStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_COMPLETE);
    }

    public function testHandleCommandWithoutChange(): void
    {
        $this->applicationCompletion->setFinancialEvidenceStatus(ApplicationCompletionEntity::STATUS_COMPLETE);

        $this->expectStatusUnchanged(ApplicationCompletionEntity::STATUS_COMPLETE);
    }
}
