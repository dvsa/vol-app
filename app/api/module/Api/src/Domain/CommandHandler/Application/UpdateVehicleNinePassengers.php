<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Transfer\Command\Application\UpdateVehicleNinePassengers as Command;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;

final class UpdateVehicleNinePassengers extends AbstractUpdateApplication
{
    protected array $sections = [
        'psvOperateLarge',
    ];
    protected string $confirmMessage = 'vehicle nine passengers updated';

    protected function updateApplication(ApplicationEntity $application, Command|CommandInterface $command): void
    {
        $application->setPsvNoSmallVhlConfirmation($command->getPsvNoSmallVhlConfirmation());
    }
}
