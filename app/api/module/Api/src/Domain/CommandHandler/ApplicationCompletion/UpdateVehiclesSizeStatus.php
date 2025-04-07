<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationCompletion;

use Doctrine\ORM\Query;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Dvsa\Olcs\Api\Entity\Application\Application;

final class UpdateVehiclesSizeStatus extends AbstractUpdateStatus
{
    protected $repoServiceName = 'Application';

    protected $section = 'VehiclesSize';

    protected function isSectionValid(Application $application): bool
    {
        return $application->getPsvWhichVehicleSizes() !== null;
    }
}
