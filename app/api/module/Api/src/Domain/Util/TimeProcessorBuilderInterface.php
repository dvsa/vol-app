<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Util;

use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTimeCalculatorInterface;
use Dvsa\Olcs\Api\Entity\System\Sla;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea;

interface TimeProcessorBuilderInterface
{
    public function build(Sla $sla, ?TrafficArea $trafficArea): DateTimeCalculatorInterface;
}
