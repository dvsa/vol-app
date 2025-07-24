<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Transfer\Command\Application\UpdateVehicleOperatingSmall as Command;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;

final class UpdateVehicleOperatingSmall extends AbstractUpdateApplication
{
    protected array $sections = [
        'psvOperateSmall',
    ];
    protected string $confirmMessage = 'vehicle operating small updated';

    protected function updateApplication(ApplicationEntity $application, Command|CommandInterface $command): void
    {
        $application->setPsvOperateSmallVhl($command->getPsvOperateSmallVhl());
    }
}
