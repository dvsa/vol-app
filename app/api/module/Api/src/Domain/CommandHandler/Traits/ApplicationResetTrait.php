<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Traits;

use DateTime;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Entity\Application\Application;

trait ApplicationResetTrait
{
    /**
     * Normalise the received date into a format suitable to be passed to the create application/variation command
     */
    private function processReceivedDate(mixed $receivedDate): ?string
    {
        if ($receivedDate !== null) {
            if ($receivedDate instanceof DateTime) {
                $receivedDate = $receivedDate->format('Y-m-d');
            }

            return $receivedDate;
        }

        return null;
    }

    /**
     * Close all open tasks relating to the application/variation
     * @throws RuntimeException
     */
    private function closeTasks(Application $application): int
    {
        $count = 0;

        foreach ($application->getTasks() as $task) {
            if ($task->getIsClosed() === 'N') {
                $count++;
                $task->setIsClosed('Y');
            }
        }

        $this->applicationRepo->save($application);

        return $count;
    }

    /**
     * Deletes the association between the application and its operating centres
     * @throws RuntimeException
     */
    private function removeAssociationOfApplicationAndOperatingCentres(Application $application): int
    {
        $count = 0;
        $applicationOperatingCentres = $application->getOperatingCentres();

        if (empty($applicationOperatingCentres)) {
            return $count;
        }

        foreach ($applicationOperatingCentres as $aoc) {
            $this->applicationOperatingCentreRepo->delete($aoc);
            $count++;
        }
        return $count;
    }
}
