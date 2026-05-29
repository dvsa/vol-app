<?php

declare(strict_types=1);

/**
 * Update Financial Evidence Status Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\ApplicationCompletion;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\ApplicationCompletion\UpdatePsvDocumentaryEvidenceLargeStatus as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationCompletion\UpdatePsvDocumentaryEvidenceLargeStatus;
use Mockery as m;
use Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion as ApplicationCompletionEntity;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;

/**
 * Update Financial Evidence Status Test
 *
 * @author Teja Vaddala <Teja.vaddala@dvsa.gov.uk>
 */
class UpdatePsvDocumentaryEvidenceLargeStatusTest extends AbstractUpdateStatusTestCase
{
    protected $section = 'PsvDocumentaryEvidenceLarge';

    public function setUp(): void
    {
        $this->sut = new UpdatePsvDocumentaryEvidenceLargeStatus();
        $this->command = Cmd::create(['id' => 111]);

        parent::setUp();
    }

    public function testHandleCommandWithUploadLaterSetsIncomplete(): void
    {
        $this->applicationCompletion->setPsvDocumentaryEvidenceLargeStatus(
            ApplicationCompletionEntity::STATUS_COMPLETE
        );

        $this->application
            ->shouldReceive('getOccupationEvidenceUploaded')
            ->andReturn(ApplicationEntity::FINANCIAL_EVIDENCE_UPLOAD_LATER);

        $this->expectStatusChange(
            ApplicationCompletionEntity::STATUS_INCOMPLETE
        );
    }
}
