<?php

/**
 * Query to get short notice by Bus Reg
 */

namespace Dvsa\Olcs\Transfer\Query\Bus;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldType;

/**
 * @Transfer\RouteName("backend/bus/single/short-notice")
 */
class ShortNoticeByBusReg extends AbstractQuery
{
    use FieldType\Identity;
}
