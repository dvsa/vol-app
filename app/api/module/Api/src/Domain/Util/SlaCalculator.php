<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Util;

use Dvsa\Olcs\Api\Entity\System\Sla;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea;
use Olcs\Logging\Log\Logger;

class SlaCalculator implements SlaCalculatorInterface
{
    public function __construct(private readonly TimeProcessorBuilderInterface $timeProcessorBuilder)
    {
    }

    public function applySla(\DateTime $date, Sla $sla, ?TrafficArea $trafficArea = null): \DateTime
    {
        Logger::debug(
            'BEGIN SLA for ' . $sla->getField() . ' ' . $sla->getDays() . ' days from ' .
            $sla->getCompareTo()
        );
        // generate the appropriate object to do the calculation determined by the sla and whether to take weekends
        // and/or public holidays into account
        $dateTimeProcessor = $this->timeProcessorBuilder->build($sla, $trafficArea);

        $targetDate = $dateTimeProcessor->calculateDate($date, $sla->getDays());
        Logger::debug("FINAL TARGET DATE => " . $targetDate->format('d/m/Y'));
        Logger::debug('END SLA' . "\n");

        return $targetDate;
    }
}
