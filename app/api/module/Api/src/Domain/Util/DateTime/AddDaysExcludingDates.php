<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Util\DateTime;

use Olcs\Logging\Log\Logger;

class AddDaysExcludingDates implements DateTimeCalculatorInterface
{
    public function __construct(private readonly DateTimeCalculatorInterface $wrapped, private readonly DateProviderInterface $excluded)
    {
    }

    public function calculateDate(\DateTime $date, int $days): \DateTime
    {
        Logger::debug('AddDaysExcludingDates : Calculating SLA date ' . $days . ' days from ' . $date->format('d-m-Y'));

        // calculate using AddWorkingDays after weekend days have been added
        $endDate = $date;
        $processedHolidays = [];
        $count = 0;
        while ($days !== 0 || $count > 10) {
            Logger::debug('Recursion ' . $count . "\n");
            Logger::debug('days => ' . $days);

            $wdEndDate = $this->wrapped->calculateDate($endDate, $days);

            Logger::debug('new endDate => ' . $wdEndDate->format('d/m/Y'));
            Logger::debug(
                'Getting holidays to exclude between ' . $endDate->format('d/m/Y') . ' and ' . $wdEndDate->format(
                    'd/m/Y'
                )
            );

            $countingBackwards = false;
            if ($endDate > $wdEndDate) {
                $countingBackwards = true;
                // if we are counting backwards then the dates need to be in the correct order so we switch the
                // parameters around
                $excludedDates = $this->excluded->between($wdEndDate, $endDate);
            } else {
                $excludedDates = $this->excluded->between($endDate, $wdEndDate);
            }

            $excludedDateCount = 0;
            foreach ($excludedDates as $ed) {
                if (!in_array($ed['publicHolidayDate'], $processedHolidays)) {
                    $processedHolidays[] = $ed['publicHolidayDate'];
                    Logger::debug('Excluding date -> ' . $ed['publicHolidayDate']);
                    $excludedDateCount++;
                } else {
                    Logger::debug('Skipping date -> ' . $ed['publicHolidayDate']);
                }
            }

            $endDate = $wdEndDate;

            $days = $excludedDateCount;
            if ($countingBackwards && $days) {
                $days = -$days;
            }

            Logger::debug('END Recursion ' . $count . "\n\n");
            $count++;
        }

        return $endDate;
    }
}
