<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Transfer\Command\Application\UpdateMainOccupationEvidence as Command;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;

final class UpdateMainOccupationEvidence extends AbstractUpdateApplication
{
    protected array $sections = [
        'psvDocumentaryEvidenceLarge',
    ];
    protected string $confirmMessage = 'main occupation evidence updated';

    protected function updateApplication(ApplicationEntity $application, Command|CommandInterface $command): void
    {
        $application->setOccupationEvidenceUploaded($command->getEvidenceUploadType());
    }
}
