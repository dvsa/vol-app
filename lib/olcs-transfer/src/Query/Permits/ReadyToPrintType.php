<?php

/**
 * Get a list of permit types ready to print
 */

namespace Dvsa\Olcs\Transfer\Query\Permits;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/permits/ready-to-print-type")
 */
final class ReadyToPrintType extends AbstractQuery
{
}
