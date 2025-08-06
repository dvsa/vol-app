<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationCompletion;

use Dvsa\Olcs\Api\Entity\Application\Application;

final class UpdateVehiclesSizeStatus extends AbstractUpdateStatus
{
    protected $section = 'VehiclesSize';

    protected function isSectionValid(Application $application): bool
    {
        return $application->isSectionCompleted($this->section);
    }
}
