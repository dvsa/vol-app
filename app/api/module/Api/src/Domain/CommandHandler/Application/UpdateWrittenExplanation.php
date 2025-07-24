<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Transfer\Command\Application\UpdateWrittenExplanation as Command;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;

final class UpdateWrittenExplanation extends AbstractUpdateApplication
{
    protected array $sections = [
        'psvSmallPartWritten',
    ];
    protected string $confirmMessage = 'vehicle small part written updated';

    protected function updateApplication(ApplicationEntity $application, Command|CommandInterface $command): void
    {
        $application->updateWrittenEvidence(
            $command->getPsvSmallVhlNotes(),
            $command->getPsvTotalVehicleSmall(),
            $command->getPsvTotalVehicleLarge(),
        );
    }
}
