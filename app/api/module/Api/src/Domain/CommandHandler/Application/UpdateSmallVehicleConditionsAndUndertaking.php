<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Transfer\Command\Application\UpdateSmallVehicleConditionsAndUndertaking as Command;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;

final class UpdateSmallVehicleConditionsAndUndertaking extends AbstractUpdateApplication
{
    protected array $sections = [
        'psvSmallConditions',
    ];
    protected string $confirmMessage = 'small vehicle conditions updated';

    protected function updateApplication(ApplicationEntity $application, Command|CommandInterface $command): void
    {
        $application->setPsvSmallVhlConfirmation($command->getPsvSmallVhlConfirmation());
    }
}
