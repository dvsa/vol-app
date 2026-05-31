<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationCompletion;

use Dvsa\Olcs\Api\Entity\Application\Application;

final class UpdatePsvDocumentaryEvidenceSmallStatus extends AbstractUpdateStatus
{
    protected $section = 'PsvDocumentaryEvidenceSmall';

    #[\Override]
    protected function isSectionValid(Application $application): bool
    {
        if ($application->getSmallVehicleEvidenceUploaded() === Application::FINANCIAL_EVIDENCE_UPLOAD_LATER) {
            return false;
        }

        return true;
    }
}
