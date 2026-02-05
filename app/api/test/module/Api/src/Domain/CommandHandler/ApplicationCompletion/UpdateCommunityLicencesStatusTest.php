<?php

declare(strict_types=1);

/**
 * Update Community Licences Status Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\ApplicationCompletion;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\ApplicationCompletion\UpdateCommunityLicencesStatus as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationCompletion\UpdateCommunityLicencesStatus;
use Mockery as m;
use Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion as ApplicationCompletionEntity;

/**
 * Update Community Licences Status Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class UpdateCommunityLicencesStatusTest extends AbstractUpdateStatusTestCase
{
    protected $section = 'CommunityLicences';

    public function setUp(): void
    {
        $this->sut = new UpdateCommunityLicencesStatus();
        $this->command = Cmd::create(['id' => 111]);

        parent::setUp();
    }

    public function testHandleCommandWithChange(): void
    {
        $this->applicationCompletion->setCommunityLicencesStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_COMPLETE);
    }

    public function testHandleCommandWithoutChange(): void
    {
        $this->applicationCompletion->setCommunityLicencesStatus(ApplicationCompletionEntity::STATUS_COMPLETE);

        $this->expectStatusUnchanged(ApplicationCompletionEntity::STATUS_COMPLETE);
    }
}
