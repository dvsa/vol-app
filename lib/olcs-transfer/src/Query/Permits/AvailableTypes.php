<?php

/**
 * Get list of all available permit types
 */

namespace Dvsa\Olcs\Transfer\Query\Permits;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/permits/available-types")
 */
class AvailableTypes extends AbstractQuery
{
}
