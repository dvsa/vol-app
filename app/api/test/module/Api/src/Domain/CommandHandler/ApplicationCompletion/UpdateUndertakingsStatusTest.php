<?php

declare(strict_types=1);

/**
 * Update Undertakings Status Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\ApplicationCompletion;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\ApplicationCompletion\UpdateUndertakingsStatus as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationCompletion\UpdateUndertakingsStatus;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Mockery as m;
use Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion as ApplicationCompletionEntity;

/**
 * Update Undertakings Status Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class UpdateUndertakingsStatusTest extends AbstractUpdateStatusTestCase
{
    protected $section = 'Undertakings';

    public function setUp(): void
    {
        $this->sut = new UpdateUndertakingsStatus();
        $this->command = Cmd::create(['id' => 111]);
        $this->mockedSmServices = [
            \LmcRbacMvc\Service\AuthorizationService::class => m::mock(\LmcRbacMvc\Service\AuthorizationService::class)
        ];

        parent::setUp();
    }

    public function testHandleCommandWithChange(): void
    {
        $this->setupIsInternalUser(false);
        $this->applicationCompletion->setUndertakingsStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->application->setDeclarationConfirmation('N');

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommandWithoutChange(): void
    {
        $this->setupIsInternalUser(false);
        $this->applicationCompletion->setUndertakingsStatus(ApplicationCompletionEntity::STATUS_INCOMPLETE);

        $this->application->setDeclarationConfirmation('N');

        $this->expectStatusUnchanged(ApplicationCompletionEntity::STATUS_INCOMPLETE);
    }

    public function testHandleCommand(): void
    {
        $this->setupIsInternalUser(false);
        $this->applicationCompletion->setUndertakingsStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->application->setDeclarationConfirmation('Y');

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_COMPLETE);
    }

    public function testHandleCommandForInternalUser(): void
    {
        $this->setupIsInternalUser();
        $this->applicationCompletion->setDeclarationsInternalStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->application->setAuthSignature('Y');

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_COMPLETE);
    }

    public function testHandleCommandVerified(): void
    {
        $this->setupIsInternalUser(false);
        $this->applicationCompletion->setDeclarationsInternalStatus(ApplicationCompletionEntity::STATUS_NOT_STARTED);

        $this->application->setAuthSignature('N');
        $this->application->setSignatureType(\Dvsa\Olcs\Api\Entity\Application\Application::SIG_DIGITAL_SIGNATURE);
        $this->application->setDigitalSignature(new \Dvsa\Olcs\Api\Entity\DigitalSignature());

        $this->expectStatusChange(ApplicationCompletionEntity::STATUS_COMPLETE);
    }
}
