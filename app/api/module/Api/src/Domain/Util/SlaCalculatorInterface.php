<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Util;

use Dvsa\Olcs\Api\Entity\System\Sla;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea;

interface SlaCalculatorInterface
{
    public function applySla(\DateTime $date, Sla $sla, ?TrafficArea $trafficArea): \DateTime;
}
