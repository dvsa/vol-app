<?php

declare(strict_types=1);

/**
 * Update Financial Evidence Status Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\ApplicationCompletion;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\ApplicationCompletion\UpdatePsvDocumentaryEvidenceSmallStatus as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationCompletion\UpdatePsvDocumentaryEvidenceSmallStatus;
use Mockery as m;
use Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion as ApplicationCompletionEntity;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;

/**
 * Update Financial Evidence Status Test
 *
 * @author Teja Vaddala <Teja.vaddala@dvsa.gov.uk>
 */
class UpdatePsvDocumentaryEvidenceSmallStatusTest extends AbstractUpdateStatusTestCase
{
    protected $section = 'PsvDocumentaryEvidenceSmall';

    public function setUp(): void
    {
        $this->sut = new UpdatePsvDocumentaryEvidenceSmallStatus();
        $this->command = Cmd::create(['id' => 111]);

        parent::setUp();
    }

    public function testHandleCommandWithUploadLaterSetsIncomplete(): void
    {
        $this->applicationCompletion->setPsvDocumentaryEvidenceSmallStatus(
            ApplicationCompletionEntity::STATUS_COMPLETE
        );

        $this->application
            ->shouldReceive('getSmallVehicleEvidenceUploaded')
            ->andReturn(ApplicationEntity::FINANCIAL_EVIDENCE_UPLOAD_LATER);

        $this->expectStatusChange(
            ApplicationCompletionEntity::STATUS_INCOMPLETE
        );
    }
}
