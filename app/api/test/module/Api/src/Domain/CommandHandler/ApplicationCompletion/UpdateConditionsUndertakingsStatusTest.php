<?php

declare(strict_types=1);

/**
 * Update Conditions Undertakings Status Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\ApplicationCompletion;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\ApplicationCompletion\UpdateConditionsUndertakingsStatus as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationCompletion\UpdateConditionsUndertakingsStatus;
use Mockery as m;
use Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion as ApplicationCompletionEntity;

/**
 * Update Conditions Undertakings Status Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class UpdateConditionsUndertakingsStatusTest extends AbstractUpdateStatusTestCase
{
    protected $section = 'ConditionsUndertakings';

    public function setUp(): void
    {
        $this->sut = new UpdateConditionsUndertakingsStatus();
        $this->command = Cmd::create(['id' => 111]);

        parent::setUp();
    }

    public function testHandleCommandWithChange(): void
    {
        $this->applicationCompletion->setConditionsUndertakingsStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_COMPLETE);
    }

    public function testHandleCommandWithoutChange(): void
    {
        $this->applicationCompletion->setConditionsUndertakingsStatus(ApplicationCompletionEntity::STATUS_COMPLETE);

        $this->expectStatusUnchanged(ApplicationCompletionEntity::STATUS_COMPLETE);
    }
}
