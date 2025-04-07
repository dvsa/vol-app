<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Transfer\Command\Application\UpdateNoveltyVehicles as Command;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;

final class UpdateNoveltyVehicles extends AbstractUpdateApplication
{
    protected array $sections = [
        'psvOperateNovelty',
    ];
    protected string $confirmMessage = 'novelty vehicles updated';

    protected function updateApplication(ApplicationEntity $application, Command|CommandInterface $command): void
    {
        $application->updatePsvNoveltyVehicles(
            $command->getPsvLimousines(),
            $command->getPsvNoLimousineConfirmation(),
            $command->getPsvOnlyLimousinesConfirmation()
        );
    }
}
