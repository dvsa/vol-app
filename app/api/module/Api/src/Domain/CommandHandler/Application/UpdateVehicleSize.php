<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Application\UpdateVehicleSize as Command;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;

final class UpdateVehicleSize extends AbstractUpdateApplication
{
    protected array $sections = [
        'vehiclesSize',
    ];
    protected string $confirmMessage = 'vehicle size updated';

    public function updateApplication(ApplicationEntity $application, Command|CommandInterface $command): void
    {
        $application->updatePsvVehicleSize($this->getRepo()->getRefdataReference($command->getPsvVehicleSize()));
    }
}
