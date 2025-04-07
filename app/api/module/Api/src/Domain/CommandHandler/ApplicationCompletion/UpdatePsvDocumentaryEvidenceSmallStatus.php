<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationCompletion;

use Dvsa\Olcs\Api\Entity\Application\Application;

final class UpdatePsvDocumentaryEvidenceSmallStatus extends AbstractUpdateStatus
{
    protected $section = 'PsvDocumentaryEvidenceSmall';

    protected function isSectionValid(Application $application): bool
    {
        return $application->isSectionCompleted($this->section);
    }
}
