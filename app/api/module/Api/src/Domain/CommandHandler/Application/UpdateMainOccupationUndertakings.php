<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Transfer\Command\Application\UpdateMainOccupationUndertakings as Command;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;

final class UpdateMainOccupationUndertakings extends AbstractUpdateApplication
{
    protected array $sections = [
        'psvMainOccupationUndertakings',
    ];
    protected string $confirmMessage = 'main occupation undertakings updated';

    protected function updateApplication(ApplicationEntity $application, Command|CommandInterface $command): void
    {
        $application->updateMainOccupationUndertakings(
            $command->getPsvOccupationRecordsConfirmation(),
            $command->getPsvIncomeRecordsConfirmation(),
        );
    }
}
