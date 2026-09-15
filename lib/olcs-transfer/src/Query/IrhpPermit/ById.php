<?php

/**
 * Get a single IRHP Permit by Id
 *
 * @author Andy Newton <andy@vitri.ltd>
 */

namespace Dvsa\Olcs\Transfer\Query\IrhpPermit;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

/**
 * @Transfer\RouteName("backend/irhp-permits/single")
 */
class ById extends AbstractQuery
{
    use Identity;
}
