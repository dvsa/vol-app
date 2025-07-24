<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Transfer\Command\Application\UpdateSmallVehicleEvidence as Command;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;

final class UpdateSmallVehicleEvidence extends AbstractUpdateApplication
{
    protected array $sections = [
        'psvDocumentaryEvidenceSmall',
    ];
    protected string $confirmMessage = 'small vehicle evidence updated';

    protected function updateApplication(ApplicationEntity $application, Command|CommandInterface $command): void
    {
        $application->setSmallVehicleEvidenceUploaded($command->getEvidenceUploadType());
    }
}
