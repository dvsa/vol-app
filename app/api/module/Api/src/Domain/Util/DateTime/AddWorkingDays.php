<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Util\DateTime;

use Olcs\Logging\Log\Logger;

class AddWorkingDays implements DateTimeCalculatorInterface
{
    public function __construct(private readonly DateTimeCalculatorInterface $wrapped)
    {
    }

    public function calculateDate(\DateTime $date, int $days): \DateTime
    {
        // ensure date is set to midnight to avoid date discrepancies
        $date->setTime(0, 0, 0);

        $workingDate = new \DateTime();
        Logger::debug('DIFF date -> ' . $date->format('r'));

        $workingDate->setTimestamp(strtotime("$days weekdays", $date->getTimestamp()));
        Logger::debug('workingDate -> ' . $workingDate->format('r'));

        $totalDays = (int)$date->diff($workingDate)->format('%r%a');

        Logger::debug('AddWorkingDays : processing working days -> ' . $totalDays);

        return $this->wrapped->calculateDate($date, $totalDays);
    }
}
