<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\ApplicationCompletion;

use Dvsa\Olcs\Api\Domain\Command\ApplicationCompletion\UpdateDeclarationsInternalStatus as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationCompletion\UpdateDeclarationsInternalStatus;
use Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion as ApplicationCompletionEntity;

/**
 * Update Conditions Undertakings Status Test
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class UpdateDeclarationsInternalStatusTest extends AbstractUpdateStatusTestCase
{
    protected $section = 'DeclarationsInternal';

    public function setUp(): void
    {
        $this->sut = new UpdateDeclarationsInternalStatus();
        $this->command = Cmd::create(['id' => 111]);

        parent::setUp();
    }

    public function testHandleCommandWithChange(): void
    {
        $this->applicationCompletion->setDeclarationsInternalStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandWithoutChange(): void
    {
        $this->applicationCompletion->setConditionsUndertakingsStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);
        $this->application->setAuthSignature(true);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_COMPLETE);
    }
}
