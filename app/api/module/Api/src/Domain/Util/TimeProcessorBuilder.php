<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Util;

use Dvsa\Olcs\Api\Domain\Repository\PublicHoliday;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTimeCalculatorInterface;
use Dvsa\Olcs\Api\Entity\System\Sla;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea;
use Dvsa\Olcs\Api\Domain\Util\DateTime\AddDays;
use Dvsa\Olcs\Api\Domain\Util\DateTime\AddDaysExcludingDates;
use Dvsa\Olcs\Api\Domain\Util\DateTime\AddWorkingDays;
use Dvsa\Olcs\Api\Domain\Util\DateTime\PublicHolidayDateProvider;

class TimeProcessorBuilder implements TimeProcessorBuilderInterface
{
    public function __construct(private readonly PublicHoliday $publicHolidayRepo)
    {
    }

    public function build(Sla $sla, ?TrafficArea $trafficArea): DateTimeCalculatorInterface
    {
        $dateTimeProcessor = new AddDays();
        if ($sla->getWeekend()) {
            $dateTimeProcessor = new AddWorkingDays($dateTimeProcessor);
        }

        if ($sla->getPublicHoliday()) {
            $dateProvider = new PublicHolidayDateProvider($this->publicHolidayRepo, $trafficArea);
            $dateTimeProcessor = new AddDaysExcludingDates($dateTimeProcessor, $dateProvider);
        }

        return $dateTimeProcessor;
    }
}
