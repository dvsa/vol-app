<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Util\DateTime;

use Olcs\Logging\Log\Logger;

class AddDays implements DateTimeCalculatorInterface
{
    public function calculateDate(\DateTime $date, int $days): \DateTime
    {
        // ensure date is set to midnight to avoid date discrepancies
        $date->setTime(0, 0, 0);

        Logger::debug('AddDays : processing days -> ' . $days);
        $endDate = clone $date;
        if ($days > 0) {
            $endDate->add(new \DateInterval('P' . $days . 'D'));
        } else {
            $endDate->sub(new \DateInterval('P' . abs($days) . 'D'));
        }

        return $endDate;
    }
}
