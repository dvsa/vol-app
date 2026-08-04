<?php

/**
 * Bus Reg Decision
 */

namespace Dvsa\Olcs\Transfer\Query\Bus;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\CacheableShortTermQueryInterface;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldType;

/**
 * @Transfer\RouteName("backend/bus/single/decision")
 */
class BusRegDecision extends AbstractQuery implements CacheableShortTermQueryInterface
{
    use FieldType\Identity;
}
