<?php

declare(strict_types=1);

/**
 * Update Psv Main Occupation Undertaking Status Test
 *
 * @author Teja Vaddala <teja.vaddala@dvsa.gov.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\ApplicationCompletion;

use Dvsa\Olcs\Api\Domain\Command\ApplicationCompletion\UpdatePsvMainOccupationUndertakingsStatus as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationCompletion\UpdatePsvMainOccupationUndertakingsStatus;
use Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion as ApplicationCompletionEntity;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;

/**
 * Update Psv Main Occupation Undertaking Status Test
 *
 * @author Teja Vaddala <Teja.vaddala@dvsa.gov.uk>
 */
class UpdatePsvMainOccupationUndertakingsStatusTest extends AbstractUpdateStatusTestCase
{
    protected $section = 'PsvMainOccupationUndertakings';

    public function setUp(): void
    {
        $this->sut = new UpdatePsvMainOccupationUndertakingsStatus();
        $this->command = Cmd::create(['id' => 111]);

        parent::setUp();
    }

    public function testHandleCommandWithUploadLaterSetsIncomplete(): void
    {
        $this->applicationCompletion->setPsvMainOccupationUndertakingsStatus(
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
