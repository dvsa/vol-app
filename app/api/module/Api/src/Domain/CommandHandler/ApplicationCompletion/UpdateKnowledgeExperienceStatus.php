<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationCompletion;

use Dvsa\Olcs\Api\Entity\Application\Application;

final class UpdateKnowledgeExperienceStatus extends AbstractUpdateStatus
{
    protected $section = 'KnowledgeExperience';

    #[\Override]
    protected function isSectionValid(Application $application): bool
    {
        return $application->isSectionCompleted($this->section);
    }
}