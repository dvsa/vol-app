<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Transfer\Command\Application\UpdateKnowledgeExperience as Command;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

final class UpdateKnowledgeExperience extends AbstractUpdateApplication
{
    protected array $sections = [
        'knowledgeExperience',
    ];

    protected string $confirmMessage = 'knowledge experience updated';

    #[\Override]
    protected function updateApplication(
        ApplicationEntity $application,
        Command|CommandInterface $command
    ): void {
        $application->setKnowledgeExperienceEvidenceUploaded(
            $command->getEvidenceUploadType()
        );

        $application->setKnowledgeExperienceOlat(
            $command->getKnowledgeExperienceOlat()
        );
    }
}