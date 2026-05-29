<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationCompletion;

use Dvsa\Olcs\Api\Entity\Application\Application;

final class UpdatePsvDocumentaryEvidenceLargeStatus extends AbstractUpdateStatus
{
    protected $section = 'PsvDocumentaryEvidenceLarge';

    #[\Override]
    protected function isSectionValid(Application $application): bool
    {
        if ($application->getOccupationEvidenceUploaded() == Application::FINANCIAL_EVIDENCE_UPLOAD_LATER) {
            return false;
        }
        
        return true;
    }
}
