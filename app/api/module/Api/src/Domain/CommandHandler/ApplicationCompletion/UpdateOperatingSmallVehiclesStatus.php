<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationCompletion;

use Dvsa\Olcs\Api\Entity\Application\Application;

final class UpdateOperatingSmallVehiclesStatus extends AbstractUpdateStatus
{
    protected $section = 'OperatingSmallVehicles';

    #[\Override]
    protected function isSectionValid(Application $application): bool
    {
        return $application->getPsvOperateSmallVhl() !== null;
    }
}
